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

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Feature;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\GrpcClient\BaseClient;
use Hyperf\GrpcClient\GrpcClient;
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

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingSpanEnabled('grpc')) {
            return $proceedingJoinPoint->process();
        }

        /** @var BaseClient $instance */
        $instance = $proceedingJoinPoint->getInstance();
        /** @var GrpcClient $grpcClient */
        $grpcClient = $instance->_getGrpcClient();
        [$serverAddress, $serverPort] = (fn () => [$this->host ?? null, $this->port ?? null])->call($grpcClient);

        $method = $proceedingJoinPoint->arguments['keys']['method'];
        $options = $proceedingJoinPoint->arguments['keys']['options'];
        $data = [
            'rpc.system' => 'grpc',
            'rpc.method' => $method,
            'rpc.options' => $options,
            'server.address' => (string) ($serverAddress ?? 'unknown'),
            'server.port' => $serverPort,
        ];

        $parent = SentrySdk::getCurrentHub()->getSpan();

        // No parent span or not sampled, skip tracing
        if (! $parent || ! $parent->getSampled()) {
            return $proceedingJoinPoint->process();
        }

        // Inject sentry-trace header for distributed tracing
        $options['headers'] = ($options['headers'] ?? []) + [
            Constants::SENTRY_TRACE => $parent->toTraceparent(),
            Constants::BAGGAGE => $parent->toBaggage(),
        ];

        // Inject tracing headers
        $proceedingJoinPoint->arguments['keys']['options'] = $options;

        return trace(
            fn (Scope $scope) => $proceedingJoinPoint->process(),
            SpanContext::make()
                ->setOp('rpc.client')
                ->setDescription($method)
                ->setOrigin('auto.rpc')
                ->setData($data)
        );
    }
}
