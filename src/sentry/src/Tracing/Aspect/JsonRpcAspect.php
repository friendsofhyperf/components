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
use Throwable;

use function Sentry\getBaggage;

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

        if ($proceedingJoinPoint->methodName === '__generateRpcPath') {
            $path = $proceedingJoinPoint->process();
            $key = "JsonRPC send [{$path}]";

            $context = SpanContext::create($key);
            Context::set(static::CONTEXT, $context);
            Context::set(static::DATA, [
                'coroutine.id' => Coroutine::id(),
                'rpc.path' => $path,
            ]);

            if ($this->container->has(Rpc\Context::class)) {
                $rpcContext = $this->container->get(Rpc\Context::class);

                $rpcContext->set(TraceContext::RPC_CARRIER, [
                    'sentry-trace' => TraceContext::getTransaction()->getTraceId(),
                    'baggage' => getBaggage(),
                ]);
            }

            return $path;
        }

        if ($proceedingJoinPoint->methodName === 'send') {
            /** @var array $data */
            $data = Context::get(static::DATA);
            $data['arguments'] = $proceedingJoinPoint->arguments['keys'];

            try {
                $result = $proceedingJoinPoint->process();
                // $data['result'] = $result;
            } catch (Throwable $e) {
                if (! $this->switcher->isExceptionIgnored($e)) {
                    $data = array_merge($data, [
                        'error' => true,
                        'exception.class' => get_class($e),
                        'exception.message' => $e->getMessage(),
                        'exception.code' => $e->getCode(),
                        'exception.stacktrace' => $e->getTraceAsString(),
                    ]);
                }
                throw $e;
            } finally {
                /** @var SpanContext $context */
                if ($context = Context::get(static::CONTEXT)) {
                    $data['rpc.status'] = isset($result['result']) ? 'OK' : 'Failed';
                    $context->setData($data)->finish();
                }
            }

            return $result;
        }

        return $proceedingJoinPoint->process();
    }
}
