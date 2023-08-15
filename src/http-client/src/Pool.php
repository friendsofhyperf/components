<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\Client;

use GuzzleHttp\Utils;

/**
 * @mixin Factory
 */
class Pool
{
    /**
     * The factory instance.
     *
     * @var Factory
     */
    protected $factory;

    /**
     * The handler function for the Guzzle client.
     *
     * @var callable
     */
    protected $handler;

    /**
     * The pool of requests.
     *
     * @var array
     */
    protected $pool = [];

    /**
     * Create a new requests pool.
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->handler = Utils::chooseHandler();
    }

    /**
     * Add a request to the pool with a numeric index.
     *
     * @param string $method
     * @param array $parameters
     * @return PendingRequest|\GuzzleHttp\Promise\Promise
     */
    public function __call($method, $parameters)
    {
        return $this->pool[] = $this->asyncRequest()->{$method}(...$parameters);
    }

    /**
     * Add a request to the pool with a key.
     *
     * @return PendingRequest
     */
    public function as(string $key)
    {
        return $this->pool[$key] = $this->asyncRequest();
    }

    /**
     * Retrieve the requests in the pool.
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->pool;
    }

    /**
     * Retrieve a new async pending request.
     *
     * @return PendingRequest
     */
    protected function asyncRequest()
    {
        return $this->factory->setHandler($this->handler)->async();
    }
}
