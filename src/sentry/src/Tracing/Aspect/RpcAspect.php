<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Util\Carrier;
use FriendsOfHyperf\Sentry\Util\SocketOptionContainer;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Contract\Socket\SocketOptionInterface;
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\Pool\RpcConnection;
use Hyperf\Rpc;
use Hyperf\Rpc\Contract\TransporterInterface;
use Hyperf\RpcClient;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Tappable\tap;

/**
 * @property string $prototype
 */
class RpcAspect extends AbstractAspect
{
    public const SPAN_CONTEXT = 'sentry.tracing.rpc.span_context';

    public array $classes = [
        RpcClient\AbstractServiceClient::class . '::__generateRpcPath',
        RpcClient\Client::class . '::send',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingSpanEnabled('rpc')) {
            return $proceedingJoinPoint->process();
        }

        return match ($proceedingJoinPoint->methodName) {
            '__generateRpcPath' => $this->handleGenerateRpcPath($proceedingJoinPoint),
            'send' => $this->handleSend($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    private function handleGenerateRpcPath(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var string $path */
        $path = $proceedingJoinPoint->process();
        $config = $this->container->get(ConfigInterface::class);
        /** @var string $package */
        $package = Str::camel($config->get('app_name', 'package'));
        /** @var string $service */
        $service = $proceedingJoinPoint->getInstance()->getServiceName();
        $prototype = (fn () => $this->prototype ?? 'jsonrpc')->call($proceedingJoinPoint->getInstance());
        $system = match (true) {
            str_contains($prototype, 'multiplex') => 'multiplex-rpc',
            str_contains($prototype, 'jsonrpc') => 'jsonrpc',
            default => 'rpc',
        };

        Context::set(static::SPAN_CONTEXT, SpanContext::make()
            ->setOp('rpc.client')
            ->setDescription($path)
            ->setOrigin('auto.rpc')
            ->setData([
                'rpc.system' => $system,
                'rpc.service' => $service,
                'rpc.method' => $proceedingJoinPoint->arguments['keys']['methodName'] ?? '',
            ]));

        return $path;
    }

    private function handleSend(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var null|SpanContext $spanContext */
        $spanContext = Context::get(static::SPAN_CONTEXT);

        if (! $spanContext) {
            return $proceedingJoinPoint->process();
        }

        /** @var null|TransporterInterface $transporter */
        $transporter = $proceedingJoinPoint->getInstance()->getTransporter();
        /** @var null|SocketOptionInterface $socketOptions */
        $socketOptions = null;

        if ($transporter instanceof JsonRpcPoolTransporter) {
            $contextId = spl_object_hash($transporter) . '.Connection';
            /** @var null|RpcConnection $connection */
            $connection = Context::get($contextId);
            if ($connection instanceof RpcConnection) {
                /** @var null|SocketInterface $socket */
                $socket = (fn () => $this->connection ?? null)->call($connection);
                $socketOptions = SocketOptionContainer::get($socket);
            }
        }

        try {
            return trace(
                function (Scope $scope) use ($proceedingJoinPoint, $socketOptions) {
                    $span = $scope->getSpan();
                    if ($span && $this->container->has(Rpc\Context::class)) {
                        $rpcCtx = $this->container->get(Rpc\Context::class);
                        $carrier = Carrier::fromSpan($span);
                        // Inject the RPC context data into span.
                        $span->setData([
                            'rpc.context' => $rpcCtx->getData(),
                            'server.address' => $socketOptions?->getHost(),
                            'server.port' => $socketOptions?->getPort(),
                        ]);
                        // Inject the tracing carrier into RPC context.
                        $rpcCtx->set(Constants::TRACE_CARRIER, $carrier->toJson());
                    }
                    return tap($proceedingJoinPoint->process(), function ($result) use ($span) {
                        if ($span && $this->feature->isTracingTagEnabled('rpc.result')) {
                            $span->setData(['rpc.result' => $result]);
                        }
                    });
                },
                $spanContext
            );
        } finally {
            Context::destroy(static::SPAN_CONTEXT);
        }
    }
}
