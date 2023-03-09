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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions as GuzzleHttpClientOptions;
use GuzzleHttp\Utils;
use Http\Adapter\Guzzle7\Client as GuzzleHttpClient;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\Coroutine;
use Sentry\HttpClient\HttpClientFactory;

class SentryHttpClientFactoryAspect extends AbstractAspect
{
    public array $classes = [
        HttpClientFactory::class . '::resolveClient',
        GuzzleHttpClient::class . '::buildClient',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            $proceedingJoinPoint->getInstance() instanceof HttpClientFactory
            && class_exists(GuzzleHttpClient::class)
        ) {
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

            return GuzzleHttpClient::createWithConfig($guzzleConfig);
        }

        if ($proceedingJoinPoint->getInstance() instanceof GuzzleHttpClient) {
            if (
                extension_loaded('swoole')
                && Coroutine::inCoroutine()
                && (\Swoole\Runtime::getHookFlags() & SWOOLE_HOOK_NATIVE_CURL) == 0
            ) {
                $handlerStack = new HandlerStack(new CoroutineHandler());
            } else {
                $handlerStack = new HandlerStack(Utils::chooseHandler());
            }

            $handlerStack->push(Middleware::prepareBody(), 'prepare_body');

            /** @var array $config */
            $config = $proceedingJoinPoint->arguments['keys']['config'] ?? [];
            $config = array_merge(['handler' => $handlerStack], $config);

            return new GuzzleClient($config);
        }

        return $proceedingJoinPoint->process();
    }
}
