<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Stringable;

use FriendsOfHyperf\Macros\Foundation\HtmlString;

class ToHtmlString
{
    public function __invoke()
    {
        return function () {
            return new HtmlString($this->value);
        };
    }
}
