<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
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
        if (defined('BASE_PATH')) {
            $string = str_replace(rtrim(BASE_PATH, '/') . '/', '', $string);
        }

        return $string;
    }
}
