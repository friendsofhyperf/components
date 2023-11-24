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

    public ?int $priority = PHP_INT_MAX;

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
        if (! $this->switcher->isTracingEnable('coroutine')) {
            return $proceedingJoinPoint->process();
        }

        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $callingOnFunction = CoroutineBacktraceHelper::foundCallingOnFunction();
        $parent = $this->startSpan('coroutine.create', $callingOnFunction);
        if ($this->tagManager->has('coroutine.id')) {
            $parent->setData([
                $this->tagManager->get('coroutine.id') => Co::id(),
            ]);
        }

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $parent, $callingOnFunction) {
            $transaction = $this->startCoroutineTransaction(
                $parent,
                name: 'coroutine',
                op: 'coroutine.execute',
                description: $callingOnFunction,
            );

            defer(function () use ($transaction) {
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
                $transaction->setData($data);
            }
        };
        $parent->finish();

        return $proceedingJoinPoint->process();
    }
}
