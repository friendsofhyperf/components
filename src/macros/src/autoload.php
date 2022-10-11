<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\HttpServer\Request;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Composer;
use Hyperf\Utils\Str;
use Hyperf\Utils\Stringable;

$namespace = 'FriendsOfHyperf\\Macros\\Macros';

foreach (Composer::getLoader()->getClassMap() as $class => $path) {
    if (! str_starts_with($class, $namespace)) {
        continue;
    }

    if (str_ends_with($class, 'Macro')) {
        $class = substr($class, 0, -5);
    }

    if (! $class) {
        continue;
    }

    $name = lcfirst(class_basename($class));

    match (true) {
        str_starts_with($class, $namespace . '\\Arr\\') => ! Arr::hasMacro($name) && Arr::macro($name, (new $class())()),
        str_starts_with($class, $namespace . '\\Collection\\') => ! Collection::hasMacro($name) && Collection::macro($name, (new $class())()),
        str_starts_with($class, $namespace . '\\Request\\') => ! Request::hasMacro($name) && Request::macro($name, (new $class())()),
        str_starts_with($class, $namespace . '\\Str\\') => ! Str::hasMacro($name) && Str::macro($name, (new $class())()),
        str_starts_with($class, $namespace . '\\Stringable\\') => ! Stringable::hasMacro($name) && Stringable::macro($name, (new $class())()),
        default => null
    };
}
