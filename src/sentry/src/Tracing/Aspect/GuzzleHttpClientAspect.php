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

use Error;
use FriendsOfHyperf\Sentry\Switcher;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function FriendsOfHyperf\Sentry\trace;

/**
 * @method array getConfig()
 * @property array $config
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
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
        if (! $this->switcher->isTracingSpanEnabled('guzzle')) {
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

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $options, $guzzleConfig) {
                $span = $scope->getSpan();

                if (! $span) {
                    return $proceedingJoinPoint->process();
                }

                // Inject trace context
                $options['headers'] = array_replace($options['headers'] ?? [], [
                    'sentry-trace' => $span->toTraceparent(),
                    'baggage' => $span->toBaggage(),
                    'traceparent' => $span->toW3CTraceparent(),
                ]);

                // Override the headers
                $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];
                $onStats = $options['on_stats'] ?? null;

                // Add or override the on_stats option to record the request duration.
                $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($options, $guzzleConfig, $onStats, $span) {
                    $request = $stats->getRequest();
                    $uri = $request->getUri();
                    $span->setData([
                        // See: https://develop.sentry.dev/sdk/performance/span-data-conventions/#http
                        'http.query' => $uri->getQuery(),
                        'http.fragment' => $uri->getFragment(),
                        'http.request.method' => $request->getMethod(),
                        'http.request.body.size' => strlen($options['body'] ?? ''),
                        'http.request.full_url' => (string) $request->getUri(),
                        'http.request.path' => $request->getUri()->getPath(),
                        'http.request.scheme' => $request->getUri()->getScheme(),
                        'http.request.host' => $request->getUri()->getHost(),
                        'http.request.port' => $request->getUri()->getPort(),
                        'http.request.user_agent' => $request->getHeaderLine('User-Agent'), // updated key for consistency
                        'http.request.headers' => $request->getHeaders(),
                        // Other
                        'coroutine.id' => Coroutine::id(),
                        'http.system' => 'guzzle',
                        'http.guzzle.config' => $guzzleConfig,
                        'http.guzzle.options' => $options ?? [],
                        'duration' => $stats->getTransferTime() * 1000, // in milliseconds
                    ]);

                    if ($response = $stats->getResponse()) {
                        $span->setData([
                            'http.response.status_code' => $response->getStatusCode(),
                            'http.response.reason' => $response->getReasonPhrase(),
                            'http.response.headers' => $response->getHeaders(),
                            'http.response.body.size' => $response->getBody()->getSize() ?? 0,
                            'http.response_content_length' => $response->getHeaderLine('Content-Length'),
                            'http.decoded_response_content_length' => $response->getHeaderLine('X-Decoded-Content-Length'),
                            'http.response_transfer_size' => $response->getHeaderLine('Content-Length'),
                        ]);

                        if ($this->switcher->isTracingExtraTagEnabled('http.response.body.contents')) {
                            $isTextual = \preg_match(
                                '/^(text\/|application\/(json|xml|x-www-form-urlencoded))/i',
                                $response->getHeaderLine('Content-Type')
                            ) === 1;
                            $body = $response->getBody();

                            if ($isTextual && $body->isSeekable()) {
                                $pos = $body->tell();
                                $span->setData([
                                    'http.response.body.contents' => \GuzzleHttp\Psr7\Utils::copyToString($body, 8192), // 8KB 上限
                                ]);
                                $body->seek($pos);
                            } else {
                                $span->setData(['http.response.body.contents' => '[binary omitted]']);
                            }
                        }

                        $span->setStatus(
                            SpanStatus::createFromHttpStatusCode($response->getStatusCode())
                        );

                        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 600) {
                            $span->setTags([
                                'error' => 'true',
                                'http.response.reason' => $response->getReasonPhrase(),
                            ]);
                        }
                    }

                    if ($stats->getHandlerErrorData()) {
                        $exception = $stats->getHandlerErrorData() instanceof Throwable
                            ? $stats->getHandlerErrorData()
                            : new Error();
                        $span->setStatus(SpanStatus::internalError())
                            ->setTags([
                                'error' => 'true',
                                'exception.class' => $exception::class,
                                'exception.code' => (string) $exception->getCode(),
                            ])
                            ->setData([
                                'exception.message' => $exception->getMessage(),
                            ]);
                        if ($this->switcher->isTracingExtraTagEnabled('exception.stack_trace')) {
                            $span->setData([
                                'exception.stack_trace' => (string) $exception,
                            ]);
                        }
                    }

                    if (is_callable($onStats)) {
                        ($onStats)($stats);
                    }
                };

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('http.client')
                ->setDescription($request->getMethod() . ' ' . (string) $request->getUri())
                ->setOrigin('auto.http.client')
                ->setData(['coroutine.id' => Coroutine::id()])
        );
    }
}
