<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\GuzzleAdapter\Client;
use GuzzleHttp\RequestOptions as GuzzleHttpClientOptions;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\HttpClient\HttpClientFactory;

class SentryHttpClientFactoryAspect extends AbstractAspect
{
    public array $classes = [
        HttpClientFactory::class . '::resolveClient',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var \Sentry\Options $options */
        $options = $proceedingJoinPoint->arguments['keys']['options'];

        $guzzleConfig = [
            GuzzleHttpClientOptions::TIMEOUT => $options->getHttpTimeout(),
            GuzzleHttpClientOptions::CONNECT_TIMEOUT => $options->getHttpConnectTimeout(),
            'no_aspect' => true,
        ];

        if ($options->getHttpProxy() !== null) {
            $guzzleConfig[GuzzleHttpClientOptions::PROXY] = $options->getHttpProxy();
        }

        return Client::createWithConfig($guzzleConfig);
    }
}
