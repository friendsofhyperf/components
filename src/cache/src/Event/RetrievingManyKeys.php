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

class RetrievingManyKeys extends CacheEvent
{
    /**
     * The keys that are being retrieved.
     *
     * @var array
     */
    public $keys;

    /**
     * Create a new event instance.
     *
     * @param string|null $storeName
     * @param array $keys
     */
    public function __construct($storeName, $keys)
    {
        parent::__construct($storeName, $keys[0] ?? '');

        $this->keys = $keys;
    }
}
