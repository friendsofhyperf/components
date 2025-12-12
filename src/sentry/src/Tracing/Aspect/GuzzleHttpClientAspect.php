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
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
    private const MAX_RESPONSE_BODY_SIZE = 8192; // 8 KB

    public array $classes = [
        Client::class . '::transfer',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingSpanEnabled('guzzle')) {
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
            function (Scope $scope) use ($proceedingJoinPoint, $options, $guzzleConfig, $request) {
                if ($span = $scope->getSpan()) {
                    // Inject trace context
                    $options['headers'] = array_replace($options['headers'] ?? [], [
                        Constants::BAGGAGE => $span->toBaggage(),
                        Constants::SENTRY_TRACE => $span->toTraceparent(),
                    ]);

                    // Override the headers
                    $proceedingJoinPoint->arguments['keys']['options']['headers'] = $options['headers'];
                    $onStats = $options['on_stats'] ?? null;

                    // Add or override the on_stats option to record the request duration.
                    $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($options, $guzzleConfig, $onStats, $request, $span) {
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
                            'http.system' => 'guzzle',
                            'http.guzzle.config' => $guzzleConfig,
                            'http.guzzle.options' => $options ?? [],
                            'duration' => $stats->getTransferTime() * 1000, // in milliseconds
                            'server.address' => $request->getUri()->getHost(),
                            'server.port' => $request->getUri()->getPort(),
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

                            if ($this->feature->isTracingTagEnabled('http.response.body.contents')) {
                                $span->setData([
                                    'http.response.body.contents' => $this->getResponsePayload($response, $options),
                                ]);
                            }

                            $span->setHttpStatus($response->getStatusCode());
                        }

                        if ($stats->getHandlerErrorData()) {
                            $span->setStatus(SpanStatus::internalError());
                        }

                        if (is_callable($onStats)) {
                            ($onStats)($stats);
                        }
                    };
                }

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('http.client')
                ->setDescription($request->getMethod() . ' ' . (string) $request->getUri())
                ->setOrigin('auto.http.client')
        );
    }

    protected function getResponsePayload(ResponseInterface $response, array $options = []): mixed
    {
        if (isset($options['stream']) && $options['stream'] === true) {
            return '[Streamed Response]';
        }

        // Determine if the response is textual based on the Content-Type header.
        $contentType = $response->getHeaderLine('Content-Type');
        $isTextual = $contentType === '' || \preg_match(
            '/^(text\/|application\/(json|xml|x-www-form-urlencoded|grpc))/i',
            $contentType
        ) === 1;

        // If the response is not textual or the stream is not seekable, we will return a placeholder.
        if (! $isTextual) {
            return '[Binary Omitted]';
        }

        $stream = $response->getBody();
        $pos = null;

        try {
            if ($stream->isSeekable()) {
                $pos = $stream->tell();
                $stream->rewind();
            }

            $content = \GuzzleHttp\Psr7\Utils::copyToString(
                $stream,
                self::MAX_RESPONSE_BODY_SIZE + 1 // 多读 1 byte 用来判断是否截断
            );

            if (strlen($content) > self::MAX_RESPONSE_BODY_SIZE) {
                return substr(
                    $content,
                    0,
                    self::MAX_RESPONSE_BODY_SIZE
                ) . '… [truncated]';
            }

            return $content === '' ? '[Empty-String Response]' : $content;
        } catch (Throwable $e) {
            return '[Error Retrieving Response Content]';
        } finally {
            if ($pos !== null) {
                try {
                    $stream->seek($pos);
                } catch (Throwable) {
                    // ignore: tracing must not break the request flow
                }
            }
        }
    }
}
