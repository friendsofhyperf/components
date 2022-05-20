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

class KeyWritten extends CacheEvent
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @var null|int
     */
    public $seconds;

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int $seconds
     */
    public function __construct($key, $value, $seconds = null)
    {
        parent::__construct($key);

        $this->value = $value;
        $this->seconds = $seconds;
    }
}
