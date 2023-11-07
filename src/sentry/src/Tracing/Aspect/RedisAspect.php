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

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('redis')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];

        $context = SpanContext::create(
            sprintf('redis.%s', $arguments['name']),
            sprintf('%s::%s()', $proceedingJoinPoint->className, $arguments['name'])
        );
        $data = [
            'redis.coroutine.id' => Coroutine::id(),
            'redis.pool' => (fn () => $this->poolName)->call($proceedingJoinPoint->getInstance()),
            'redis.arguments' => $arguments,
        ];
        $tags = [];
        $status = SpanStatus::ok();

        try {
            $result = $proceedingJoinPoint->process();
            $data['redis.result'] = $result;
        } catch (Throwable $exception) {
            $status = SpanStatus::internalError();
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            $data['redis.exception.stack_trace'] = (string) $exception;

            throw $exception;
        } finally {
            $context->setStatus($status)
                ->setTags($tags)
                ->setData($data)
                ->finish();
        }

        return $result;
    }
}
