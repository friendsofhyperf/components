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
use FriendsOfHyperf\Sentry\SentryContext;
use FriendsOfHyperf\Sentry\Util\CoroutineBacktraceHelper;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\startTransaction;
use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Coroutine\defer;
use function Sentry\continueTrace;

class CoroutineAspect extends AbstractAspect
{
    public const CONTEXT_KEYS = [
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function __construct(protected Feature $feature)
    {
        $this->priority = PHP_INT_MAX;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->feature->isTracingSpanEnabled('coroutine')
            || SentryContext::isTracingDisabled()
        ) {
            return $proceedingJoinPoint->process();
        }

        $callingOnFunction = CoroutineBacktraceHelper::foundCallingOnFunction();

        if (! $callingOnFunction) {
            return $proceedingJoinPoint->process();
        }

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $callingOnFunction) {
                if ($span = $scope->getSpan()) {
                    $cid = Co::id();
                    $callable = $proceedingJoinPoint->arguments['keys']['callable'];

                    // Transfer the Sentry context to the new coroutine.
                    $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $span, $callingOnFunction, $cid) {
                        SentrySdk::init(); // Ensure Sentry is initialized in the new coroutine.

                        // Restore the Context in the new Coroutine.
                        foreach (self::CONTEXT_KEYS as $key) {
                            Context::getOrSet($key, fn () => Context::get($key, coroutineId: $cid));
                        }

                        // Start a new transaction for the coroutine preparation phase.
                        $transaction = startTransaction(
                            continueTrace($span->toTraceparent(), $span->toBaggage())
                                ->setName('coroutine')
                                ->setOp('coroutine.prepare')
                                ->setDescription($callingOnFunction)
                                ->setOrigin('auto.coroutine')
                        );

                        // Defer the finishing of the transaction and flushing of events until the coroutine completes.
                        defer(function () use ($transaction) {
                            $transaction->finish();
                            Integration::flushEvents();
                        });

                        return trace(
                            fn () => $callable(),
                            SpanContext::make()
                                ->setOp('coroutine.execute')
                                ->setDescription($callingOnFunction)
                                ->setOrigin('auto.coroutine')
                        );
                    };
                }

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('coroutine.create')
                ->setDescription($callingOnFunction)
                ->setOrigin('auto.coroutine')
        );
    }
}
