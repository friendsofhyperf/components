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
    public function __construct(private ConfigInterface $config)
    {
    }

    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return false;
        }

        [$type, $key] = explode('.', $key, 2);
        $tags = $this->fetchTags($type);

        return isset($tags[$key]);
    }

    public function get(string $key): string
    {
        if (! str_contains($key, '.')) {
            return $key;
        }

        [$type, $key] = explode('.', $key, 2);
        $tags = $this->fetchTags($type);

        return $tags[$key] ?? $type . '.' . $key;
    }

    private function fetchTags(string $type, mixed $default = []): array
    {
        return $this->config->get(
            sprintf('sentry.tracing.tags.%s', $type),
            $default
        );
    }
}
