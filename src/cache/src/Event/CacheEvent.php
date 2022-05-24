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
