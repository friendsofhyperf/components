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

class WritingManyKeys extends CacheEvent
{
    /**
     * The keys that are being written.
     *
     * @var mixed
     */
    public $keys;

    /**
     * The value that is being written.
     *
     * @var mixed
     */
    public $values;

    /**
     * The number of seconds the keys should be valid.
     *
     * @var int|null
     */
    public $seconds;

    /**
     * Create a new event instance.
     *
     * @param string|null $storeName
     * @param array $keys
     * @param array $values
     * @param int|null $seconds
     */
    public function __construct($storeName, $keys, $values, $seconds = null)
    {
        parent::__construct($storeName, $keys[0]);

        $this->keys = $keys;
        $this->values = $values;
        $this->seconds = $seconds;
    }
}
