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

use FriendsOfHyperf\Telescope\Storage\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use GuzzleHttp\Client;
use Hyperf\Collection\Arr;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @property array $config
 */
class HttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            ! $this->telescopeConfig->isEnable('guzzle')
            || ! TelescopeContext::getBatchId()
        ) {
            return $proceedingJoinPoint->process();
        }

        $startTime = microtime(true);
        $instance = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->arguments;
        $options = $arguments['keys']['options'] ?? [];
        $guzzleConfig = (fn () => $this->config ?? [])->call($instance);

        if (($options['no_telescope_aspect'] ?? null) === true || ($guzzleConfig['no_telescope_aspect'] ?? null) === true) {
            return $proceedingJoinPoint->process();
        }

        // Disable the aspect for the requestAsync method.
        if ($proceedingJoinPoint->methodName == 'request') {
            $proceedingJoinPoint->arguments['keys']['options']['no_telescope_aspect'] = true;
        }

        $arguments = $proceedingJoinPoint->arguments;
        $method = $arguments['keys']['method'] ?? 'GET';
        $uri = $arguments['keys']['uri'] ?? '';
        $headers = $options['headers'] ?? [];
        $result = $proceedingJoinPoint->process();
        $response = [];

        if ($result instanceof ResponseInterface) {
            $response['status'] = $result->getStatusCode();
            $response['reason'] = $result->getReasonPhrase();
            $response['headers'] = $result->getHeaders();
            $response['body'] = $this->getResponsePayload($result);
            $result->getBody()->rewind();
        }

        Telescope::recordClientRequest(IncomingEntry::make([
            'method' => $method,
            'uri' => $uri,
            'headers' => $headers,
            'response_status' => $response['status'] ?? 0,
            'response_headers' => $response['headers'] ?? '',
            'response' => $response,
            'duration' => floor((microtime(true) - $startTime) * 1000),
        ]));

        return $result;
    }

    public function getResponsePayload(ResponseInterface $response)
    {
        $stream = $response->getBody();
        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $content = $stream->getContents();
        } catch (Throwable $e) {
            return 'Purged By Hyperf Telescope: ' . $e->getMessage();
        }

        if (is_string($content)) {
            if (! $this->contentWithinLimits($content)) {
                return 'Purged By Hyperf Telescope';
            }
            if (
                is_array(json_decode($content, true))
                && json_last_error() === JSON_ERROR_NONE
            ) {
                return $this->contentWithinLimits($content) /* @phpstan-ignore-line */
                ? $this->hideParameters(json_decode($content, true), Telescope::$hiddenResponseParameters)
                : 'Purged By Hyperf Telescope';
            }
            if (Str::startsWith(strtolower($response->getHeaderLine('content-type') ?: ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Hyperf Telescope'; /* @phpstan-ignore-line */
            }
            if (Str::contains($response->getHeaderLine('content-type'), 'application/grpc') !== false) {
                return TelescopeContext::getGrpcResponsePayload() ?: 'Purged By Hyperf Telescope';
            }
        }

        if (empty($content)) {
            return 'Empty Response';
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
