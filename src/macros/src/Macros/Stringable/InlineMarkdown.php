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

use Hyperf\Utils\Str;

class InlineMarkdown
{
    public function __invoke()
    {
        return function (array $options = []) {
            return new static(Str::inlineMarkdown($this->value, $options));
        };
    }
}
