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

class WritingKey extends CacheEvent
{
    /**
     * The value that will be written.
     *
     * @var mixed
     */
    public $value;

    /**
     * The number of seconds the key should be valid.
     *
     * @var int|null
     */
    public $seconds;

    /**
     * Create a new event instance.
     *
     * @param string|null $storeName
     * @param string $key
     * @param mixed $value
     * @param int|null $seconds
     */
    public function __construct($storeName, $key, $value, $seconds = null)
    {
        parent::__construct($storeName, $key);

        $this->value = $value;
        $this->seconds = $seconds;
    }
}
