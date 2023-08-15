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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions as GuzzleHttpClientOptions;
use GuzzleHttp\Utils;
use Http\Adapter\Guzzle6\Client as Guzzle6HttpClient;
use Http\Adapter\Guzzle7\Client as Guzzle7HttpClient;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Guzzle\CoroutineHandler;
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
            'no_sentry_aspect' => true,
        ];

        if ($options->getHttpProxy() !== null) {
            $guzzleConfig[GuzzleHttpClientOptions::PROXY] = $options->getHttpProxy();
        }

        return match (true) {
            class_exists(Guzzle7HttpClient::class) => $this->createHttpClientWithConfig(Guzzle7HttpClient::class, $guzzleConfig),
            class_exists(Guzzle6HttpClient::class) => $this->createHttpClientWithConfig(Guzzle6HttpClient::class, $guzzleConfig),
            default => $proceedingJoinPoint->process(),
        };
    }

    /**
     * @return ClientInterface|HttpAsyncClientInterface
     */
    private function createHttpClientWithConfig(string $class, array $config)
    {
        if (extension_loaded('swoole') && Coroutine::inCoroutine()) {
            $handlerStack = new HandlerStack(new CoroutineHandler());
        } else {
            $handlerStack = new HandlerStack(Utils::chooseHandler());
        }

        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new $class(new GuzzleClient($config));
    }
}
