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

class EnsurePunctuation
{
    /**
     * Ensures the given string ends with punctuation.
     *
     * @param string $string
     * @return string
     */
    public function __invoke($string)
    {
        if (! str($string)->endsWith(['.', '?', '!', ':'])) {
            return "{$string}.";
        }

        return $string;
    }
}
