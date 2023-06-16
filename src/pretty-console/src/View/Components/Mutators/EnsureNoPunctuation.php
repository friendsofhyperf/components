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

use function Hyperf\Stringable\str;

class EnsureNoPunctuation
{
    /**
     * Ensures the given string does not end with punctuation.
     *
     * @param string $string
     * @return string
     */
    public function __invoke($string)
    {
        if (str($string)->endsWith(['.', '?', '!', ':'])) {
            return substr_replace($string, '', -1);
        }

        return $string;
    }
}
