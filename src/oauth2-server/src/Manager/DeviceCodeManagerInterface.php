<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager;

use FriendsOfHyperf\Oauth2\Server\Model\DeviceCodeInterface;

interface DeviceCodeManagerInterface
{
    public function save(DeviceCodeInterface $deviceCode): void;

    public function findByDeviceCode(string $deviceCode): ?DeviceCodeInterface;
}
