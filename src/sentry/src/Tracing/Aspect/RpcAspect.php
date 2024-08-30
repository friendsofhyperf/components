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
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc;
use Hyperf\RpcClient;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @property string $prototype
 */
class RpcAspect extends AbstractAspect
{
    use SpanStarter;

    public const SPAN = 'sentry.tracing.rpc.span';

    protected const DATA = 'sentry.tracing.rpc.data';

    public array $classes = [
        RpcClient\AbstractServiceClient::class . '::__generateRpcPath',
        RpcClient\Client::class . '::send',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected CarrierPacker $packer
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('rpc')) {
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

        // $package.$service/$path
        $op = sprintf('%s.%s/%s', $package, $service, $path);
        $span = $this->startSpan($op, $path);

        if (! $span) {
            return $path;
        }

        $data = [
            'coroutine.id' => Coroutine::id(),
            'rpc.system' => $system,
            'rpc.method' => $proceedingJoinPoint->arguments['keys']['methodName'] ?? '',
            'rpc.service' => $service,
        ];

        Context::set(static::DATA, $data); // will be removed in v3.2
        Context::set(static::SPAN, $span->setData($data));

        if ($this->container->has(Rpc\Context::class)) {
            $this->container->get(Rpc\Context::class)->set(Constants::TRACE_CARRIER, $this->packer->pack($span));
        }

        return $path;
    }

    private function handleSend(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $data = (array) Context::get(static::DATA, []);
        $data['rpc.arguments'] = $proceedingJoinPoint->arguments['keys'];

        if ($this->container->has(Rpc\Context::class)) {
            $data['rpc.context'] = $this->container->get(Rpc\Context::class)->getData();
        }

        // TODO
        // 'server.address' => '',
        // 'server.port' => '',

        /** @var Span|null $span */
        $span = Context::get(static::SPAN);

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            if ($this->switcher->isTracingExtraTagEnable('rpc.result')) {
                $data['rpc.result'] = $result;
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();

            Context::destroy(static::SPAN);
            Context::destroy(static::DATA);
        }

        return $result;
    }
}
