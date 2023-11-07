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
use Hyperf\DB\DB;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Tracing\SpanStatus;
use Throwable;

class DbAspect extends AbstractAspect
{
    public array $classes = [
        DB::class . '::__call',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('db')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $context = SpanContext::create(
            'Db::' . $arguments['name'],
            $proceedingJoinPoint->className . '::' . $arguments['name'] . '()'
        );
        $data = [
            'db.coroutine.id' => Coroutine::id(),
            'db.query' => json_encode($arguments['arguments'], JSON_UNESCAPED_UNICODE),
        ];
        $tags = [];
        $status = SpanStatus::ok();

        try {
            $result = $proceedingJoinPoint->process();
            $data['db.result'] = json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $exception) {
            $status = SpanStatus::internalError();
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            $data['db.exception.stack_trace'] = (string) $exception;

            throw $exception;
        } finally {
            $context->setStatus($status)
                ->setData($data)
                ->setTags($tags)
                ->finish();
        }

        return $result;
    }
}
