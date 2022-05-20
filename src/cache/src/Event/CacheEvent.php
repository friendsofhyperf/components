<?php

declare(strict_types=1);
/**
 * This file is part of cache.
 *
 * @link     https://github.com/friendsofhyperf/cache
 * @document https://github.com/friendsofhyperf/cache/blob/2.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache\Event;

abstract class CacheEvent
{
    /**
     * @var string
     */
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}
