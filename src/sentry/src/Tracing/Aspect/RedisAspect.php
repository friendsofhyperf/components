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
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @property string $poolName
 */
class RedisAspect extends AbstractAspect
{
    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('redis')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $data = [];

        if ($this->tagManager->has('coroutine.id')) {
            $data[$this->tagManager->get('coroutine.id')] = Coroutine::id();
        }
        if ($this->tagManager->has('pool')) {
            $data[$this->tagManager->get('pool')] = (fn () => $this->poolName)->call($proceedingJoinPoint->getInstance());
        }
        if ($this->tagManager->has('redis.arguments')) {
            $data[$this->tagManager->get('redis.arguments')] = $arguments['arguments'];
        }

        $context = SpanContext::create(
            sprintf('redis.%s', $arguments['name']),
            sprintf('%s::%s()', $proceedingJoinPoint->className, $arguments['name'])
        );

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->has('redis.result')) {
                $data[$this->tagManager->get('redis.result')] = $result;
            }
            $context->setStatus(SpanStatus::ok());
        } catch (Throwable $exception) {
            $context->setStatus(SpanStatus::internalError());
            $context->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('redis.exception.stack_trace')) {
                $data[$this->tagManager->get('redis.exception.stack_trace')] = (string) $exception;
            }

            throw $exception;
        } finally {
            $context->setData($data)->finish();
        }

        return $result;
    }
}
