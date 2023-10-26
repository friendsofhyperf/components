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

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
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

        $data = [];

        if ($this->tagManager->has('db.coroutine.id')) {
            $data[$this->tagManager->get('db.coroutine.id')] = Coroutine::id();
        }

        if ($this->tagManager->has('db.query')) {
            $data[$this->tagManager->get('db.query')] = json_encode($arguments['arguments'], JSON_UNESCAPED_UNICODE);
        }

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->has('db.result')) {
                $data[$this->tagManager->get('db.result')] = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            $context->setStatus(SpanStatus::ok());
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
            $context->setData($data)->finish();
        }

        return $result;
    }
}
