<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob;

use FriendsOfHyperf\AmqpJob\Attempt\RedisAttempt;
use FriendsOfHyperf\AmqpJob\Contract\Attempt;
use FriendsOfHyperf\AmqpJob\Contract\Packer;
use Hyperf\Codec\Packer\PhpSerializerPacker;

final class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                Attempt::class => RedisAttempt::class,
                Packer::class => PhpSerializerPacker::class,
            ],
        ];
    }
}
