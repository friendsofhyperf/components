<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Purifier\Casts;

trait WithConfig
{
    protected array|string|null $config;

    public function __construct($config = null)
    {
        $this->config = $config;
    }
}
