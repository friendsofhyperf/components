<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Macros\Listener;

use FriendsOfHyperf\Macros\ArrMixin;
use FriendsOfHyperf\Macros\CollectionMixin;
use FriendsOfHyperf\Macros\RequestMixin;
use FriendsOfHyperf\Macros\StringableMixin;
use FriendsOfHyperf\Macros\StrMixin;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Request;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\Stringable;

class RegisterMixinListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param BootApplication $event
     */
    public function process(object $event): void
    {
        Arr::mixin(new ArrMixin());
        Collection::mixin(new CollectionMixin());
        Request::mixin(new RequestMixin());
        Str::mixin(new StrMixin());
        Stringable::mixin(new StringableMixin());
    }
}
