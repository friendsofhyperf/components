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
            'coroutine.id' => Coroutine::id(),
            'db.query' => json_encode($arguments['arguments']),
        ];

        try {
            $result = $proceedingJoinPoint->process();
            // $data['result'] = $result;
            $context->setStatus(SpanStatus::ok());
        } catch (Throwable $e) {
            $context->setStatus(SpanStatus::internalError());
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
            $context->setData($data)->finish();
        }

        return $result;
    }
}
