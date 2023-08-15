<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\PrettyConsole\View\Components\Mutators;

class EnsureRelativePaths
{
    /**
     * Ensures the given string only contains relative paths.
     *
     * @param string $string
     * @return string
     */
    public function __invoke($string)
    {
        // if (app()->has('path.base')) {
        //     $string = str_replace(base_path() . '/', '', $string);
        // }

        return $string;
    }
}
