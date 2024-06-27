<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Macros;

use FriendsOfHyperf\Support\HtmlString;
use Hyperf\Stringable\Str;
use Hyperf\Stringable\Stringable;

/**
 * @mixin Stringable
 * @property string $value
 */
class StringableMixin
{
    public function inlineMarkdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = []) => new static(Str::inlineMarkdown($this->value, $options));
    }

    public function markdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = [], array $extensions = []) => new static(Str::markdown($this->value, $options, $extensions));
    }

    public function toHtmlString()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new HtmlString($this->value);
    }

    public function whenIsAscii()
    {
        /* @phpstan-ignore-next-line */
        return fn ($callback, $default = null) => $this->when($this->isAscii(), $callback, $default);
    }
}
