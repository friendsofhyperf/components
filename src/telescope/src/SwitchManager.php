<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use Hyperf\Contract\ConfigInterface;

class SwitchManager
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function isEnable(string $key): bool
    {
        return (bool) $this->config->get("telescope.enable.{$key}", false);
    }
}
