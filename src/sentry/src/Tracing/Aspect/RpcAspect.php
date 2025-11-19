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
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc;
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
        $protocol = (fn () => $this->protocol ?? 'jsonrpc')->call($proceedingJoinPoint->getInstance());
        $system = match (true) {
            str_contains($protocol, 'multiplex') => 'multiplex-rpc',
            str_contains($protocol, 'jsonrpc') => 'jsonrpc',
            default => 'rpc',
        };

        // https://github.com/open-telemetry/semantic-conventions/blob/main/docs/rpc/rpc-spans.md
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

        try {
            return trace(
                function (Scope $scope) use ($proceedingJoinPoint) {
                    $span = $scope->getSpan();
                    if ($span && $this->container->has(Rpc\Context::class)) {
                        $rpcCtx = $this->container->get(Rpc\Context::class);
                        $carrier = Carrier::fromSpan($span);
                        // Inject the RPC context data into span.
                        $span->setData([
                            'rpc.context' => $rpcCtx->getData(),
                        ]);
                        // Inject the tracing carrier into RPC context.
                        $rpcCtx->set(Constants::TRACE_CARRIER, $carrier->toJson());
                    }
                    return tap($proceedingJoinPoint->process(), function ($result) use ($span) {
                        if ($this->feature->isTracingTagEnabled('rpc.result')) {
                            $span?->setData(['rpc.result' => $result]);
                        }
                        if (Context::has(Constants::TRACE_RPC_SERVER_ADDRESS)) {
                            $span?->setData([
                                'server.address' => Context::get(Constants::TRACE_RPC_SERVER_ADDRESS),
                                'server.port' => Context::get(Constants::TRACE_RPC_SERVER_PORT),
                            ]);
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
