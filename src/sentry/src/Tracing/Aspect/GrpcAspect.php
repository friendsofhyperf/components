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
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

class GrpcAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\GrpcClient\BaseClient::_simpleRequest',
        'Grpc\BaseStub::_simpleRequest',
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
            'coroutine.id' => Coroutine::id(),
            'grpc.method' => $method,
            'grpc.options' => $options,
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

        return trace(
            fn (Scope $scope) => $proceedingJoinPoint->process(),
            SpanContext::make()
                ->setOp('grpc.client')
                ->setDescription($method)
                ->setOrigin('auto.grpc')
                ->setData($data)
        );
    }
}
