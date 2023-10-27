<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing;

use Hyperf\Contract\ConfigInterface;

class TagManager
{
    protected array $tags;

    public function __construct(ConfigInterface $config)
    {
        $this->tags = (array) $config->get('sentry.tracing.tags', []);
    }

    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return false;
        }

        [$type, $key] = explode('.', $key, 2);

        return isset($this->tags[$type][$key]);
    }

    public function get(string $key): string
    {
        if (! str_contains($key, '.')) {
            return $key;
        }

        [$type, $key] = explode('.', $key, 2);

        return (string) ($this->tags[$type][$key] ?? $type . '.' . $key);
    }
}
