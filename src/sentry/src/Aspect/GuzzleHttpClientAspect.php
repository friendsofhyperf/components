<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Breadcrumb;

/**
 * @method array getConfig()
 * @property array $config
 */
class GuzzleHttpClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::transfer',
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // If the guzzle aspect is disabled, we will not record the request.
        if (! $this->feature->isBreadcrumbEnabled('guzzle')) {
            return $proceedingJoinPoint->process();
        }

        $options = $proceedingJoinPoint->arguments['keys']['options'] ?? [];
        $guzzleConfig = (fn () => match (true) {
            method_exists($this, 'getConfig') => $this->getConfig(), // @deprecated ClientInterface::getConfig will be removed in guzzlehttp/guzzle:8.0.
            property_exists($this, 'config') => $this->config,
            default => [],
        })->call($proceedingJoinPoint->getInstance());

        // If the no_sentry_aspect option is set to true, we will not record the request.
        if (($options['no_sentry_aspect'] ?? null) === true || ($guzzleConfig['no_sentry_aspect'] ?? null) === true) {
            return $proceedingJoinPoint->process();
        }

        $onStats = $options['on_stats'] ?? null;

        // Override the on_stats option to record the request.
        $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($onStats, $guzzleConfig, $options) {
            $request = $stats->getRequest();
            $response = $stats->getResponse();
            $uri = $request->getUri()->__toString();
            $data = [
                'config' => $guzzleConfig,
                // request
                'http.request.method' => $request->getMethod(),
                'http.request.body.size' => strlen($options['body'] ?? ''),
                'http.request.full_url' => (string) $request->getUri(),
                'http.request.path' => $request->getUri()->getPath(),
                'http.request.scheme' => $request->getUri()->getScheme(),
                'http.request.host' => $request->getUri()->getHost(),
                'http.request.port' => $request->getUri()->getPort(),
                'http.request.user_agent' => $request->getHeaderLine('User-Agent'), // updated key for consistency
                'http.request.headers' => $request->getHeaders(),
                // response
                'http.response.status_code' => $response?->getStatusCode(),
                'http.response.body.size' => $response?->getBody()->getSize() ?? 0,
                'http.response.reason' => $response?->getReasonPhrase(),
                'http.response.headers' => $response?->getHeaders(),
                'duration' => $stats->getTransferTime() * 1000,
            ];

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_HTTP,
                'http',
                $uri,
                $data
            ));

            if (is_callable($onStats)) {
                ($onStats)($stats);
            }
        };

        return $proceedingJoinPoint->process();
    }
}
