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

use Hyperf\Context\Context;

class Merge
{
    public function __invoke()
    {
        return function (array $input) {
            Context::override($this->contextkeys['parsedData'], fn ($inputs) => array_replace((array) $inputs, $input));

            return $this;
        };
    }
}
