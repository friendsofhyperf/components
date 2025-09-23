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
use FriendsOfHyperf\Sentry\Util\CoroutineBacktraceHelper;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function Hyperf\Coroutine\defer;

class CoroutineAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    protected array $keys = [
        SentrySdk::class,
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public function __construct(protected Switcher $switcher)
    {
        $this->priority = PHP_INT_MAX;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->switcher->isTracingSpanEnabled('coroutine')
            || Switcher::isDisableCoroutineTracing()
        ) {
            return $proceedingJoinPoint->process();
        }

        $callingOnFunction = CoroutineBacktraceHelper::foundCallingOnFunction();

        // Only trace the top-level coroutine creation.
        if (! $callingOnFunction) {
            return $proceedingJoinPoint->process();
        }

        // If there's no active transaction, skip tracing.
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If there's no active transaction, skip tracing.
        if (! $transaction || ! $transaction->getSampled()) {
            return $proceedingJoinPoint->process();
        }

        // Start a span for the coroutine creation.
        $parent = $transaction->startChild(
            SpanContext::make()
                ->setOp('coroutine.create')
                ->setDescription($callingOnFunction)
                ->setOrigin('auto.coroutine')
                ->setData(['coroutine.id' => Co::id()])
        );
        SentrySdk::getCurrentHub()->setSpan($parent);

        $cid = Co::id();
        $keys = $this->keys;
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];

        // Transfer the Sentry context to the new coroutine.
        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $parent, $callingOnFunction, $cid, $keys) {
            $from = Co::getContextFor($cid);
            $current = Co::getContextFor();

            foreach ($keys as $key) {
                if (isset($from[$key]) && ! isset($current[$key])) {
                    $current[$key] = $from[$key];
                }
            }

            $transaction = $this->startCoroutineTransaction(
                parent: $parent,
                name: 'coroutine',
                op: 'coroutine.execute',
                description: $callingOnFunction,
                origin: 'auto.coroutine',
            )->setData(['coroutine.id' => Co::id()]);

            defer(function () use ($transaction) {
                SentrySdk::getCurrentHub()->setSpan($transaction);
                $transaction->finish();
            });

            try {
                $callable();
            } catch (Throwable $exception) {
                $transaction->setStatus(SpanStatus::internalError())
                    ->setTags([
                        'error' => 'true',
                        'exception.class' => $exception::class,
                        'exception.message' => $exception->getMessage(),
                        'exception.code' => (string) $exception->getCode(),
                    ]);
                if ($this->switcher->isTracingExtraTagEnabled('exception.stack_trace')) {
                    $transaction->setData([
                        'exception.stack_trace' => (string) $exception,
                    ]);
                }

                throw $exception;
            }
        };

        try {
            return $proceedingJoinPoint->process();
        } finally {
            $parent->finish();
            SentrySdk::getCurrentHub()->setSpan($transaction);
        }
    }
}
