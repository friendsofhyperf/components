<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Factory;

use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface as Contract;
use Hyperf\Contract\ConfigInterface;

final class ConfigFactory implements Contract
{
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get('oauth2-server.' . $key, $default);
    }
}
