<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\GuzzleAdapter;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Utils\Coroutine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Adapter for Guzzle 6.
 */
final class Client implements HttpClient, HttpAsyncClient
{
    private ClientInterface $client;

    /**
     * If you pass a Guzzle instance as $client, make sure to configure Guzzle to not
     * throw exceptions on HTTP error status codes, or this adapter will violate PSR-18.
     * See also self::buildClient at the bottom of this class.
     */
    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?? self::buildClient();
    }

    /**
     * Factory method to create the Guzzle 6 adapter with custom Guzzle configuration.
     */
    public static function createWithConfig(array $config): Client
    {
        return new self(
            self::buildClient($config)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $promise = $this->sendAsyncRequest($request);

        return $promise->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        $promise = $this->client->sendAsync($request);

        return new Promise($promise, $request);
    }

    /**
     * Build the Guzzle client instance.
     */
    private static function buildClient(array $config = []): GuzzleClient
    {
        if (
            extension_loaded('swoole')
            && Coroutine::inCoroutine()
            && (\Swoole\Runtime::getHookFlags() & SWOOLE_HOOK_NATIVE_CURL) == 0
        ) {
            $handlerStack = HandlerStack::create(new CoroutineHandler());
        } else {
            $handlerStack = HandlerStack::create(\GuzzleHttp\choose_handler());
        }
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new GuzzleClient($config);
    }
}
