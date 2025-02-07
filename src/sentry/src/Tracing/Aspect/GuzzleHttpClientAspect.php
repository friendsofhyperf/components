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
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @method array getConfig()
 * @property array $config
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Client::class . '::transfer',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->switcher->isTracingSpanEnable('guzzle')
            || Context::get(RpcAspect::SPAN) // If the parent span is not exists or the parent span is belongs to rpc, then skip.
        ) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        /** @var RequestInterface $request */
        $request = $arguments['request'];
        $options = $arguments['options'] ?? [];
        $guzzleConfig = (fn () => match (true) {
            method_exists($this, 'getConfig') => $this->getConfig(), // @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
            property_exists($this, 'config') => $this->config,
            default => [],
        })->call($proceedingJoinPoint->getInstance());

        // If the no_sentry_tracing option is set to true, we will not record the request.
        if (
            ($options['no_sentry_tracing'] ?? null) === true
            || ($guzzleConfig['no_sentry_tracing'] ?? null) === true
        ) {
            return $proceedingJoinPoint->process();
        }

        // Inject trace context
        $parent = SentrySdk::getCurrentHub()->getSpan();
        $options['headers'] = array_replace($options['headers'] ?? [], [
            'sentry-trace' => $parent->toTraceparent(),
            'baggage' => $parent->toBaggage(),
            'traceparent' => $parent->toW3CTraceparent(),
        ]);
        $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];

        // Start span
        $span = $this->startSpan('http.client', $request->getMethod() . ' ' . (string) $request->getUri());

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $onStats = $options['on_stats'] ?? null;

        // Add or override the on_stats option to record the request duration.
        $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($arguments, $guzzleConfig, $onStats, $span) {
            $request = $stats->getRequest();
            $response = $stats->getResponse();
            $uri = $request->getUri();
            $method = $request->getMethod();
            $statusCode = $response->getStatusCode();
            $data = [
                // See: https://develop.sentry.dev/sdk/performance/span-data-conventions/#http
                'http.query' => $uri->getQuery(),
                'http.fragment' => $uri->getFragment(),
                'http.request.method' => $method,
                'http.request.body.size' => strlen($arguments['options']['body'] ?? ''),
                // Other
                'coroutine.id' => Coroutine::id(),
                'http.system' => 'guzzle',
                'http.guzzle.config' => $guzzleConfig,
                'http.guzzle.options' => $arguments['options'] ?? [],
                'duration' => $stats->getTransferTime() * 1000, // in milliseconds
                'response.status' => $statusCode,
                'response.reason' => $response->getReasonPhrase(),
                'response.headers' => $response->getHeaders(),
            ];

            if ($this->switcher->isTracingExtraTagEnable('response.body')) {
                $data['response.body'] = $response->getBody()->getContents();
                $response->getBody()->isSeekable() && $response->getBody()->rewind();
            }

            if (($exception = $stats->getHandlerErrorData()) instanceof Throwable) {
                $span->setStatus(SpanStatus::internalError());
                $span->setTags([
                    'error' => true,
                    'exception.class' => $exception::class,
                    'exception.code' => $exception->getCode(),
                ]);
                if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                    $data['exception.message'] = $exception->getMessage();
                    $data['exception.stack_trace'] = (string) $exception;
                }
            } elseif ($statusCode >= 400 && $statusCode < 600) {
                $span->setStatus(SpanStatus::internalError());
                $span->setTags([
                    'error' => true,
                    'response.reason' => $response->getReasonPhrase(),
                ]);
            }

            $span->setData($data);
            $span->finish();

            if (is_callable($onStats)) {
                ($onStats)($stats);
            }
        };

        return $proceedingJoinPoint->process();
    }
}
