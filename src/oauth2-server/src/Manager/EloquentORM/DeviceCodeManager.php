<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager\EloquentORM;

use FriendsOfHyperf\Oauth2\Server\Manager\DeviceCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\Device;
use FriendsOfHyperf\Oauth2\Server\Model\DeviceCodeInterface;

final class DeviceCodeManager implements DeviceCodeManagerInterface
{
    public function save(DeviceCodeInterface $deviceCode): void
    {
        // @phpstan-ignore-next-line
        $deviceCode->save();
    }

    public function findByDeviceCode(string $deviceCode): ?DeviceCodeInterface
    {
        // @phpstan-ignore-next-line
        return Device::query()->where('device_code', $deviceCode)->first();
    }
}
