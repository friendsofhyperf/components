<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\ArrMacros;
use FriendsOfHyperf\Macros\CollectionMacros;
use FriendsOfHyperf\Macros\RequestMacros;
use FriendsOfHyperf\Macros\StringableMacros;
use FriendsOfHyperf\Macros\StrMacros;
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use Hyperf\Utils\Stringable;

Arr::mixin(new ArrMacros());
Collection::mixin(new CollectionMacros());
Request::mixin(new RequestMacros());
Str::mixin(new StrMacros());
Stringable::mixin(new StringableMacros());
