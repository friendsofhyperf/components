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
    public function apa()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(Str::apa($this->value));
    }

    public function headline()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(Str::headline($this->value));
    }

    public function inlineMarkdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = []) => new static(Str::inlineMarkdown($this->value, $options));
    }

    public function isAscii()
    {
        /* @phpstan-ignore-next-line */
        return fn () => Str::isAscii($this->value);
    }

    public function markdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = []) => new static(Str::markdown($this->value, $options));
    }

    public function position()
    {
        /* @phpstan-ignore-next-line */
        return fn ($needle, $offset = 0, $encoding = null) => Str::position($this->value, $needle, $offset, $encoding);
    }

    /**
     * Take the first or last {$limit} characters.
     *
     * @return static
     */
    public function take()
    {
        /* @phpstan-ignore-next-line */
        return fn (int $limit) => new static(Str::take($this->value, $limit));
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
