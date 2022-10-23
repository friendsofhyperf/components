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

if (! function_exists('array_is_list')) {
    /**
     * Determine if the given value is a list of items.
     * @return bool return true if the array keys are 0 .. count($array)-1 in that order. For other arrays, it returns false. For non-arrays, it throws a TypeError.
     */
    function array_is_list(array $array): bool
    {
        if ($array === [] || $array === array_values($array)) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }
}

Arr::mixin(new ArrMacros());
Collection::mixin(new CollectionMacros());
Request::mixin(new RequestMacros());
Str::mixin(new StrMacros());
Stringable::mixin(new StringableMacros());
