<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelUidAddon\Listener;

use FriendsOfHyperf\ModelUidAddon\BlueprintMixin;
use FriendsOfHyperf\ModelUidAddon\StrMixin;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Utils\Str;

class RegisterMixinListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        Str::mixin(new StrMixin());
        Blueprint::mixin(new BlueprintMixin());
    }
}
