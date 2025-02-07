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

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
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

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // If the guzzle aspect is disabled, we will not record the request.
        if (! $this->switcher->isBreadcrumbEnable('guzzle')) {
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

        $proceedingJoinPoint->arguments['keys']['options']['on_stats'] = function (TransferStats $stats) use ($onStats, $guzzleConfig, $options) {
            $request = $stats->getRequest();
            $response = $stats->getResponse();

            $uri = $request->getUri()->__toString();
            $data = [];
            $data['config'] = $guzzleConfig;
            $data['request']['method'] = $request->getMethod();
            $data['request']['options'] = $options;
            $data['response']['status'] = $response?->getStatusCode();
            $data['response']['reason'] = $response?->getReasonPhrase();
            $data['response']['headers'] = $response?->getHeaders();
            $data['duration'] = $stats->getTransferTime() * 1000;

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'guzzle',
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
