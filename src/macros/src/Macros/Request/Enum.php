<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Request;

/**
 * @mixin \Hyperf\HttpServer\Request
 */
class Enum
{
    public function __invoke()
    {
        return function ($key, $enumClass) {
            if (
                /* @phpstan-ignore-next-line */
                $this->isNotFilled($key)
                || ! function_exists('enum_exists')
                || ! enum_exists($enumClass)
                || ! method_exists($enumClass, 'tryFrom')
            ) {
                return null;
            }

            return $enumClass::tryFrom($this->input($key));
        };
    }
}
