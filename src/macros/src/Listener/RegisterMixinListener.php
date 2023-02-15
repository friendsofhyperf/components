<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Listener;

use FriendsOfHyperf\Macros\ArrMacros;
use FriendsOfHyperf\Macros\CollectionMacros;
use FriendsOfHyperf\Macros\RequestMacros;
use FriendsOfHyperf\Macros\StringableMacros;
use FriendsOfHyperf\Macros\StrMacros;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use Hyperf\Utils\Stringable;

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
        Arr::mixin(new ArrMacros());
        Collection::mixin(new CollectionMacros());
        Request::mixin(new RequestMacros());
        Str::mixin(new StrMacros());
        Stringable::mixin(new StringableMacros());
    }
}
