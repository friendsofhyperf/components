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

use function Hyperf\Collection\collect;

/**
 * @mixin Stringable
 * @property string $value
 */
class StringableMixin
{
    public function betweenFirst()
    {
        /* @phpstan-ignore-next-line */
        return fn ($from, $to) => new static(Str::betweenFirst($this->value, $from, $to));
    }

    public function classNamespace()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(Str::classNamespace($this->value));
    }

    public function excerpt()
    {
        /* @phpstan-ignore-next-line */
        return fn ($phrase = '', $options = []) => Str::excerpt($this->value, $phrase, $options);
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

    public function isJson()
    {
        /* @phpstan-ignore-next-line */
        return fn () => Str::isJson($this->value);
    }

    public function lcfirst()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(Str::lcfirst($this->value));
    }

    public function markdown()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $options = []) => new static(Str::markdown($this->value, $options));
    }

    public function newLine()
    {
        return fn ($count = 1) => $this->append(str_repeat(PHP_EOL, $count));
    }

    public function replaceStart()
    {
        /* @phpstan-ignore-next-line */
        return fn ($search, $replace) => new static(Str::replaceStart($search, $replace, $this->value));
    }

    public function replaceEnd()
    {
        /* @phpstan-ignore-next-line */
        return fn ($search, $replace) => new static(Str::replaceEnd($search, $replace, $this->value));
    }

    public function reverse()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(Str::reverse($this->value));
    }

    public function scan()
    {
        /* @phpstan-ignore-next-line */
        return fn ($format) => collect(sscanf($this->value, $format));
    }

    public function squish()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new static(Str::squish($this->value));
    }

    public function substrReplace()
    {
        /* @phpstan-ignore-next-line */
        return fn ($replace, $offset = 0, $length = null) => new static(Str::substrReplace($this->value, $replace, $offset, $length));
    }

    public function swap()
    {
        /* @phpstan-ignore-next-line */
        return fn (array $map) => new static(strtr($this->value, $map));
    }

    public function test()
    {
        return fn ($pattern) => $this->match($pattern)->isNotEmpty();
    }

    public function toHtmlString()
    {
        /* @phpstan-ignore-next-line */
        return fn () => new HtmlString($this->value);
    }

    public function toString()
    {
        /* @phpstan-ignore-next-line */
        return fn () => $this->value;
    }

    public function ucsplit()
    {
        /* @phpstan-ignore-next-line */
        return fn () => collect(Str::ucsplit($this->value));
    }

    public function value()
    {
        /* @phpstan-ignore-next-line */
        return fn () => $this->toString();
    }

    public function whenContains()
    {
        return fn ($needles, $callback, $default = null) => $this->when($this->contains($needles), $callback, $default);
    }

    public function whenContainsAll()
    {
        return fn (array $needles, $callback, $default = null) => $this->when($this->containsAll($needles), $callback, $default);
    }

    public function whenEndsWith()
    {
        return fn ($needles, $callback, $default = null) => $this->when($this->endsWith($needles), $callback, $default);
    }

    public function whenExactly()
    {
        return fn ($needles, $callback, $default = null) => $this->when($this->exactly($needles), $callback, $default);
    }

    public function whenIs()
    {
        return fn ($pattern, $callback, $default = null) => $this->when($this->is($pattern), $callback, $default);
    }

    public function whenIsAscii()
    {
        /* @phpstan-ignore-next-line */
        return fn ($callback, $default = null) => $this->when($this->isAscii(), $callback, $default);
    }

    public function whenIsUlid()
    {
        /* @phpstan-ignore-next-line */
        return fn ($callback, $default = null) => $this->when($this->isUlid(), $callback, $default);
    }

    public function whenIsUuid()
    {
        /* @phpstan-ignore-next-line */
        return fn ($callback, $default = null) => $this->when($this->isUuid(), $callback, $default);
    }

    public function whenNotExactly()
    {
        return fn ($needles, $callback, $default = null) => $this->when(! $this->exactly($needles), $callback, $default);
    }

    public function whenStartsWith()
    {
        return fn ($needles, $callback, $default = null) => $this->when($this->startsWith($needles), $callback, $default);
    }

    public function whenTest()
    {
        return fn ($pattern, $callback, $default = null) => $this->when($this->test($pattern), $callback, $default);
    }

    public function wrap()
    {
        /* @phpstan-ignore-next-line */
        return fn ($before, $after = null) => new static(Str::wrap($this->value, $before, $after));
    }

    /**
     * Wrap a string to a given number of characters.
     *
     * @return static
     */
    public function wordWrap()
    {
        /* @phpstan-ignore-next-line */
        return fn ($characters = 75, $break = "\n", $cutLongWords = false) => new static(Str::wordWrap($this->value, $characters, $break, $cutLongWords));
    }
}
