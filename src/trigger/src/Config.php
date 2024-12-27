<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

use Hyperf\Collection\Arr;

class Config
{
    public function __construct(private array $configs = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->configs, $key, $default);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->configs, $key);
    }
}
