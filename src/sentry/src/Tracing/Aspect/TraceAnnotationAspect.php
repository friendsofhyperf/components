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
use FriendsOfHyperf\Sentry\Tracing\Annotation\Trace;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Throwable;

class TraceAnnotationAspect extends AbstractAspect
{
    use SpanStarter;

    public array $annotations = [
        Trace::class,
    ];

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Trace|null $annotation */
        $annotation = $metadata->method[Trace::class] ?? null;

        if (! $annotation || ! $parent = SentrySdk::getCurrentHub()->getSpan()) {
            return $proceedingJoinPoint->process();
        }

        $data = [];
        if ($this->tagManager->has('annotation.coroutine.id')) {
            $data[$this->tagManager->get('annotation.coroutine.id')] = Coroutine::id();
        }
        if ($this->tagManager->has('annotation.arguments')) {
            $data[$this->tagManager->get('annotation.arguments')] = $proceedingJoinPoint->arguments['keys'];
        }

        $span = $this->startSpan(
            $annotation->op ?? 'method',
            $annotation->description ?? sprintf(
                '%s::%s()',
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName
            ),
        );

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            if ($this->tagManager->has('annotation.result')) {
                $data[$this->tagManager->get('annotation.result')] = $result;
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('annotation.exception.stack_trace')) {
                $data[$this->tagManager->get('annotation.exception.stack_trace')] = (string) $exception;
            }
            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish(microtime(true));

            // Reset root span TODO 待测试，span是平级
            SentrySdk::getCurrentHub()->setSpan($parent);
        }

        return $result;
    }
}
