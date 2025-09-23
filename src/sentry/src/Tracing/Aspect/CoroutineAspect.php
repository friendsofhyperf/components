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
        // \Sentry\SentrySdk::class,
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

        if (! $callingOnFunction) {
            return $proceedingJoinPoint->process();
        }

        $keys = $this->keys;
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $parent = $this->startSpan(
            op: 'coroutine.create',
            description: $callingOnFunction,
            origin: 'auto.coroutine',
        )?->setOrigin('auto.coroutine');

        if (! $parent) {
            return $proceedingJoinPoint->process();
        }

        $parent->setData(['coroutine.id' => $cid = Co::id()]);

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

            $span = $this->startSpan(
                op: 'coroutine.execute.inner',
                description: $callingOnFunction,
                origin: 'auto.coroutine',
                asParent: true
            )?->setData(['coroutine.id' => Co::id()]);

            defer(function () use ($transaction, $span) {
                $span?->finish();

                SentrySdk::getCurrentHub()->setSpan($transaction);
                $transaction->finish();
            });

            try {
                $callable();
            } catch (Throwable $exception) {
                $span?->setStatus(SpanStatus::internalError())
                    ->setTags([
                        'error' => 'true',
                        'exception.class' => $exception::class,
                        'exception.message' => $exception->getMessage(),
                        'exception.code' => (string) $exception->getCode(),
                    ]);
                if ($this->switcher->isTracingExtraTagEnabled('exception.stack_trace')) {
                    $span?->setData([
                        'exception.stack_trace' => (string) $exception,
                    ]);
                }

                throw $exception;
            }
        };

        $parent->finish();

        return $proceedingJoinPoint->process();
    }
}
