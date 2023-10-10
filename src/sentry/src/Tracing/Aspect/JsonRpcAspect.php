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

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanContext;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc;
use Hyperf\RpcClient;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\SpanStatus;
use Throwable;

class JsonRpcAspect extends AbstractAspect
{
    protected const CONTEXT = 'sentry.tracing.rpc.context';

    protected const DATA = 'sentry.tracing.rpc.data';

    public array $classes = [
        RpcClient\AbstractServiceClient::class . '::__generateRpcPath',
        RpcClient\Client::class . '::send',
    ];

    public function __construct(protected ContainerInterface $container, protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('rpc')) {
            return $proceedingJoinPoint->process();
        }

        $parent = TraceContext::getSpan();

        if ($proceedingJoinPoint->methodName === '__generateRpcPath') {
            $path = $proceedingJoinPoint->process();
            $key = "RPC send [{$path}]";

            $context = SpanContext::create($key);
            Context::set(static::CONTEXT, $context);
            Context::set(static::DATA, [
                'coroutine.id' => Coroutine::id(),
                'rpc.path' => $path,
            ]);

            if ($this->container->has(Rpc\Context::class)) {
                $sentryTrace = $parent->toTraceparent();
                $baggage = $parent->toBaggage();
                $rpcContext = $this->container->get(Rpc\Context::class);
                $rpcContext->set(TraceContext::RPC_CARRIER, [
                    'sentry-trace' => $sentryTrace,
                    'baggage' => $baggage,
                ]);
            }

            return $path;
        }

        if ($proceedingJoinPoint->methodName === 'send') {
            /** @var array $data */
            $data = Context::get(static::DATA);
            $data['arguments'] = $proceedingJoinPoint->arguments['keys'];
            /** @var SpanContext|null $context */
            $context = Context::get(static::CONTEXT);

            try {
                $result = $proceedingJoinPoint->process();
                // $data['result'] = $result;
            } catch (Throwable $e) {
                if (! $this->switcher->isExceptionIgnored($e)) {
                    $data = array_merge($data, [
                        'exception.class' => get_class($e),
                        'exception.message' => $e->getMessage(),
                        'exception.code' => $e->getCode(),
                        'exception.stacktrace' => $e->getTraceAsString(),
                    ]);
                }
                throw $e;
            } finally {
                $context?->setStatus(
                    isset($result['result']) ? SpanStatus::ok() : SpanStatus::internalError()
                )
                    ->setData($data)
                    ->finish();
            }

            return $result;
        }

        return $proceedingJoinPoint->process();
    }
}
