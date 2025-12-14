<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Collection\Arr;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @property array $config
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::transfer',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // If the guzzle aspect is disabled or the batch id is not set, we will not record the request.
        if (
            ! $this->telescopeConfig->isEnable('guzzle')
            || ! TelescopeContext::getBatchId()
        ) {
            return $proceedingJoinPoint->process();
        }

        $options = $proceedingJoinPoint->arguments['keys']['options'] ?? [];
        $guzzleConfig = (fn () => $this->config ?? [])->call($proceedingJoinPoint->getInstance());

        // If the no_telescope_aspect option is set to true, we will not record the request.
        if (
            ($options['no_telescope_aspect'] ?? null) === true
            || ($guzzleConfig['no_telescope_aspect'] ?? null) === true
        ) {
            return $proceedingJoinPoint->process();
        }

        // Add or override the on_stats option to record the request duration.
        $onStats = $options['on_stats'] ?? null;
        $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($onStats, $options) {
            try {
                $request = $stats->getRequest();
                $response = $stats->getResponse();
                $content = [
                    'method' => $request->getMethod(),
                    'uri' => $request->getUri()->__toString(),
                    'headers' => $request->getHeaders(),
                    'duration' => floor(($stats->getTransferTime() ?? 0) * 1000),
                ];
                if ($response) {
                    $content['response_status'] = $response->getStatusCode();
                    $content['response_headers'] = $response->getHeaders();
                    $content['response_reason'] = $response->getReasonPhrase();
                    $content['response'] = $this->getResponsePayload($response, $options);
                }

                Telescope::recordClientRequest(IncomingEntry::make($content));
            } catch (Throwable $exception) {
                // We will catch the exception to prevent the request from being interrupted.
            }

            if (is_callable($onStats)) {
                $onStats($stats);
            }
        };

        return $proceedingJoinPoint->process();
    }

    public function getResponsePayload(ResponseInterface $response, array $options = []): mixed
    {
        if (isset($options['stream']) && $options['stream'] === true) {
            return 'Streamed Response';
        }

        // Determine if the response is textual based on the Content-Type header.
        $contentType = $response->getHeaderLine('Content-Type');
        $isTextual = $contentType === '' || \preg_match(
            '/^(text\/|application\/(json|xml|x-www-form-urlencoded|grpc))/i',
            $contentType
        ) === 1;

        // If the response is not textual, we will return a placeholder.
        if (! $isTextual) {
            return 'Binary Response';
        }

        $stream = $response->getBody();

        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $content = $stream->getContents();

            if (is_string($content)) {
                // Check if the content is within the size limits.
                if (! $this->contentWithinLimits($content)) {
                    return 'Purged By Hyperf Telescope';
                }
                // Try to decode JSON responses and hide sensitive parameters.
                if (
                    is_array(json_decode($content, true))
                    && json_last_error() === JSON_ERROR_NONE
                ) {
                    return $this->hideParameters(json_decode($content, true), Telescope::$hiddenResponseParameters);
                }
                // Return gRPC responses and plain text responses as is.
                if (Str::contains($response->getHeaderLine('content-type'), 'application/grpc') !== false) {
                    return TelescopeContext::getGrpcResponsePayload() ?: 'Purged By Hyperf Telescope';
                }
                // Return plain text responses as is.
                if (Str::startsWith(strtolower($response->getHeaderLine('content-type') ?: ''), 'text/plain')) {
                    return $content;
                }
            }

            if (empty($content)) {
                return 'Empty Response';
            }
        } catch (Throwable $e) {
            return 'Purged By Hyperf Telescope: ' . $e->getMessage();
        } finally {
            $stream->isSeekable() && $stream->rewind();
        }

        return 'HTML Response';
    }

    protected function contentWithinLimits(string $content): bool
    {
        $limit = 64;
        return mb_strlen($content) / 1000 <= $limit;
    }

    /**
     * Hide the given parameters.
     */
    protected function hideParameters(array $data, array $hidden): array
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }
}
