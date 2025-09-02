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
use Hyperf\Coordinator\Coordinator;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function Hyperf\Support\class_basename;

class CoordinatorAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Coordinator::class . '::yield',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $data = [
            'coroutine.id' => Coroutine::id(),
            'timeout' => $timeout = $proceedingJoinPoint->arguments['keys']['timeout'] ?? -1,
        ];

        $span = $this->startSpan(
            sprintf('%s.%s', strtolower(class_basename($proceedingJoinPoint->className)), $proceedingJoinPoint->methodName),
            sprintf('%s::%s(%s)', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $timeout),
        );

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $exception) {
            $span?->setStatus(SpanStatus::internalError());
            $span?->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }
            throw $exception;
        } finally {
            $span?->setOrigin('auto.coordinator')->setData($data)->finish(microtime(true));
        }
    }
}
