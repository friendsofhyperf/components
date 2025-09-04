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
use Sentry\Tracing\SpanStatus;
use Swoole\Http2\Response as Http2Response;
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
        if (! $this->switcher->isTracingSpanEnable('grpc')) {
            return $proceedingJoinPoint->process();
        }

        $options = $proceedingJoinPoint->arguments['keys']['options'];

        $data = [
            'grpc.method' => $method = $proceedingJoinPoint->arguments['keys']['method'],
            'grpc.options' => $options,
            'coroutine.id' => Coroutine::id(),
        ];

        $parent = SentrySdk::getCurrentHub()->getSpan();
        $options['headers'] = $options['headers'] ?? [] + [
            'sentry-trace' => $parent->toTraceparent(),
            'baggage' => $parent->toBaggage(),
            'traceparent' => $parent->toW3CTraceparent(),
        ];
        $proceedingJoinPoint->arguments['keys']['options'] = $options;

        $span = $this->startSpan(
            op: 'grpc.client',
            description: $method,
            origin: 'auto.grpc',
        )?->setData($data);

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            [$message, $code, $response] = $result;

            if ($response instanceof Http2Response) {
                $span->setData([
                    'response.status' => $code,
                    'response.reason' => $message,
                    'response.headers' => $response->headers,
                ]);
                $this->switcher->isTracingExtraTagEnable('response.body') && $span->setData([
                    'response.body' => $response->data,
                ]);
            }
        } catch (Throwable $exception) {
            $span?->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => true,
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => $exception->getCode(),
                ]);
            $this->switcher->isTracingExtraTagEnable('exception.stack_trace') && $span?->setData([
                'exception.stack_trace' => (string) $exception,
            ]);

            throw $exception;
        } finally {
            $span?->finish();
        }

        return $result;
    }
}
