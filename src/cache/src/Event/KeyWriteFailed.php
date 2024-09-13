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

class KeyWriteFailed extends CacheEvent
{
    public function __construct(
        string $storeName,
        string $key,
        public readonly mixed $value,
        public readonly ?int $seconds = null
    ) {
        parent::__construct($storeName, $key);
    }
}
