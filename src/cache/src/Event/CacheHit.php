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

class CacheHit extends CacheEvent
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __construct($key, $value)
    {
        parent::__construct($key);

        $this->value = $value;
    }
}
