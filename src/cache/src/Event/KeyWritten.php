<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache\Event;

class KeyWritten extends CacheEvent
{
    /**
     * @param string $key
     * @param mixed $value
     * @param null|int $seconds
     */
    public function __construct($key, public $value, public ?int $seconds = null)
    {
        parent::__construct($key);
    }
}
