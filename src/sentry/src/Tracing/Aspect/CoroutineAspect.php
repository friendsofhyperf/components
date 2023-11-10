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
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use FriendsOfHyperf\Sentry\Util\CoroutineBacktraceHelper;
use Hyperf\Coroutine\Coroutine as Co;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function Hyperf\Coroutine\defer;

class CoroutineAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->switcher->isTracingEnable('coroutine')
            || ! $parent = TraceContext::getSpan()
        ) {
            return $proceedingJoinPoint->process();
        }

        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $callingOnFunction = CoroutineBacktraceHelper::foundCallingOnFunction();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $parent, $callingOnFunction) {
            SentrySdk::init();

            $transaction = $this->startCoroutineTransaction($parent, [
                'name' => 'coroutine',
                'op' => 'coroutine.create',
                'description' => $callingOnFunction ?? '#' . Co::parentId(),
            ]);

            $coSpan = $this->startSpan('coroutine.execute', '#' . Co::id());

            defer(function () use ($transaction, $coSpan) {
                $coSpan->finish();
                SentrySdk::getCurrentHub()->setSpan($transaction);
                $transaction->finish();
            });

            $data = [];

            if ($this->tagManager->has('coroutine.id')) {
                $data[$this->tagManager->get('coroutine.id')] = Co::id();
            }

            try {
                $callable();
            } catch (Throwable $exception) {
                $transaction->setStatus(SpanStatus::internalError());
                $transaction->setTags([
                    'error' => true,
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => $exception->getCode(),
                ]);
                if ($this->tagManager->has('coroutine.exception.stack_trace')) {
                    $data[$this->tagManager->get('coroutine.exception.stack_trace')] = (string) $exception;
                }

                throw $exception;
            } finally {
                $coSpan->setData($data);
            }
        };

        return $proceedingJoinPoint->process();
    }
}
