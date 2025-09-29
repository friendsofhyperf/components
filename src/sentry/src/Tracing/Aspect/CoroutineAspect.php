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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Util\CoroutineBacktraceHelper;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function FriendsOfHyperf\Sentry\startTransaction;
use function Hyperf\Coroutine\defer;
use function Sentry\continueTrace;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    protected array $keys = [
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public function __construct(protected Feature $feature)
    {
        $this->priority = PHP_INT_MAX;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->feature->isTracingSpanEnabled('coroutine')
            || Feature::isDisableCoroutineTracing()
        ) {
            return $proceedingJoinPoint->process();
        }

        $callingOnFunction = CoroutineBacktraceHelper::foundCallingOnFunction();

        // Only trace the top-level coroutine creation.
        if (! $callingOnFunction) {
            return $proceedingJoinPoint->process();
        }

        // Get the current transaction from the current scope.
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If there's no active transaction, skip tracing.
        if (! $transaction?->getSampled()) {
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

            $coTransaction = startTransaction(
                continueTrace($parent->toTraceparent(), $parent->toBaggage())
                    ->setName('coroutine')
                    ->setOp('coroutine.execute')
                    ->setDescription($callingOnFunction)
                    ->setOrigin('auto.coroutine')
            );

            defer(function () use ($coTransaction) {
                // Set the transaction on the current scope to ensure it's the active one.
                SentrySdk::getCurrentHub()->setSpan($coTransaction);

                // Finish the transaction when the coroutine ends.
                $coTransaction->finish();

                // Flush events
                Integration::flushEvents();
            });

            try {
                $callable();
            } catch (Throwable $exception) {
                $coTransaction->setStatus(SpanStatus::internalError());

                throw $exception;
            }
        };

        try {
            return $proceedingJoinPoint->process();
        } finally {
            $parent->finish();
        }
    }
}
