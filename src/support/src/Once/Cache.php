<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Once;

use Countable;
use WeakMap;

/**
 * @deprecated since v3.1, use FriendsOfHyperf\Support\Once instead, will be removed in v3.2
 */
class Cache implements Countable
{
    public WeakMap $values;

    /**
     * The cache instance.
     * @var static|null
     */
    protected static $cache;

    protected bool $enabled = true;

    protected function __construct()
    {
        $this->values = new WeakMap();
    }

    public static function getInstance(): static
    {
        return static::$cache ??= new static();
    }

    public function has(object $object, string $backtraceHash): bool
    {
        if (! isset($this->values[$object])) {
            return false;
        }

        return array_key_exists($backtraceHash, $this->values[$object]);
    }

    public function get($object, string $backtraceHash): mixed
    {
        return $this->values[$object][$backtraceHash];
    }

    public function set(object $object, string $backtraceHash, mixed $value): void
    {
        $cached = $this->values[$object] ?? [];

        $cached[$backtraceHash] = $value;

        $this->values[$object] = $cached;
    }

    public function forget(object $object): void
    {
        unset($this->values[$object]);
    }

    public function flush(): self
    {
        $this->values = new WeakMap();

        return $this;
    }

    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function count(): int
    {
        return count($this->values);
    }
}
