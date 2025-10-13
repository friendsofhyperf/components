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
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\startTransaction;
use function FriendsOfHyperf\Sentry\trace;
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

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $callingOnFunction) {
                if ($callingOnFunction && $span = $scope->getSpan()) {
                    $cid = Co::id();
                    $keys = $this->keys;
                    $callable = $proceedingJoinPoint->arguments['keys']['callable'];

                    // Transfer the Sentry context to the new coroutine.
                    $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $span, $callingOnFunction, $cid, $keys) {
                        SentrySdk::init(); // Ensure Sentry is initialized in the new coroutine.

                        $from = Co::getContextFor($cid);
                        $current = Co::getContextFor();

                        foreach ($keys as $key) {
                            if (isset($from[$key]) && ! isset($current[$key])) {
                                $current[$key] = $from[$key];
                            }
                        }

                        $transaction = startTransaction(
                            continueTrace($span->toTraceparent(), $span->toBaggage())
                                ->setName('coroutine')
                                ->setOp('coroutine.prepare')
                                ->setDescription($callingOnFunction)
                                ->setOrigin('auto.coroutine')
                        );

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
