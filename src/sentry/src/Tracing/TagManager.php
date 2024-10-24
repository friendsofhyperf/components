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

/**
 * @deprecated since v3.1, will be removed in v3.2
 */
class TagManager
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function has(string $key): bool
    {
        return true;
    }

    public function get(string $key): string
    {
        return $key;
    }
}
