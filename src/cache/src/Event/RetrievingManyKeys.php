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
    public function __construct(
        string $storeName,
        public readonly array $keys
    ) {
        parent::__construct($storeName, (string) ($keys[0] ?? ''));
    }
}
