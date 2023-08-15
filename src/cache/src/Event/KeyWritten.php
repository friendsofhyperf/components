<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Cache\Event;

class KeyWritten extends CacheEvent
{
    /**
     * @param mixed $value
     */
    public function __construct(string $key, public $value, public ?int $seconds = null)
    {
        parent::__construct($key);
    }
}
