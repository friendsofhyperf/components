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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
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

class RpcAspect extends AbstractAspect
{
    protected const CONTEXT = 'sentry.tracing.rpc.context';

    protected const DATA = 'sentry.tracing.rpc.data';

    public array $classes = [
        RpcClient\AbstractServiceClient::class . '::__generateRpcPath',
        RpcClient\Client::class . '::send',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('rpc')) {
            return $proceedingJoinPoint->process();
        }

        $parent = TraceContext::getSpan();

        if ($proceedingJoinPoint->methodName === '__generateRpcPath') {
            $path = $proceedingJoinPoint->process();
            $context = SpanContext::create('rpc.send', $path);
            Context::set(static::CONTEXT, $context);

            $data = [];

            if ($this->tagManager->has('rpc.coroutine.id')) {
                $data[$this->tagManager->get('rpc.coroutine.id')] = Coroutine::id();
            }

            Context::set(static::DATA, $data);

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
            $data = (array) Context::get(static::DATA);
            if ($this->tagManager->has('rpc.arguments')) {
                $data[$this->tagManager->get('rpc.arguments')] = $proceedingJoinPoint->arguments['keys'];
            }
            /** @var SpanContext|null $context */
            $context = Context::get(static::CONTEXT);

            try {
                $result = $proceedingJoinPoint->process();
                if ($this->tagManager->has('rpc.result')) {
                    $data[$this->tagManager->get('rpc.result')] = $result;
                }
            } catch (Throwable $exception) {
                $context->setStatus(SpanStatus::internalError());
                $context->setTags([
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => $exception->getCode(),
                    'exception.stacktrace' => (string) $exception,
                ]);

                throw $exception;
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
