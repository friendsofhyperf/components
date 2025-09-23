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
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcClient\BaseClient;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

class GrpcAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        BaseClient::class . '::_simpleRequest',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnabled('grpc')) {
            return $proceedingJoinPoint->process();
        }

        $method = $proceedingJoinPoint->arguments['keys']['method'];
        $options = $proceedingJoinPoint->arguments['keys']['options'];
        $data = [
            'grpc.method' => $method,
            'grpc.options' => $options,
            'coroutine.id' => Coroutine::id(),
        ];

        $parent = SentrySdk::getCurrentHub()->getSpan();

        // No parent span or not sampled, skip tracing
        if (! $parent || ! $parent->getSampled()) {
            return $proceedingJoinPoint->process();
        }

        // Inject sentry-trace header for distributed tracing
        $options['headers'] = ($options['headers'] ?? []) + [
            'sentry-trace' => $parent->toTraceparent(),
            'baggage' => $parent->toBaggage(),
            'traceparent' => $parent->toW3CTraceparent(),
        ];

        // Inject tracing headers
        $proceedingJoinPoint->arguments['keys']['options'] = $options;

        return $this->trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                $span = $scope->getSpan();
                try {
                    return $proceedingJoinPoint->process();
                } catch (Throwable $exception) {
                    $span->setStatus(SpanStatus::internalError())
                        ->setTags([
                            'error' => 'true',
                            'exception.class' => $exception::class,
                            'exception.message' => $exception->getMessage(),
                            'exception.code' => (string) $exception->getCode(),
                        ]);
                    if ($this->switcher->isTracingExtraTagEnabled('exception.stack_trace')) {
                        $span->setData([
                            'exception.stack_trace' => (string) $exception,
                        ]);
                    }
                    throw $exception;
                }
            },
            SpanContext::make()
                ->setOp('grpc.client')
                ->setDescription($method)
                ->setOrigin('auto.grpc')
                ->setData($data)
        );
    }
}
