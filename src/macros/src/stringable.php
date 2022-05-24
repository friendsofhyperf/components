<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Foundation\HtmlString;
use Hyperf\Utils\Str;
use Hyperf\Utils\Stringable;

if (! Stringable::hasMacro('betweenFirst')) {
    Stringable::macro('betweenFirst', function ($from, $to) {
        return new static(Str::betweenFirst($this->value, $from, $to));
    });
}

if (! Stringable::hasMacro('classNamespace')) {
    Stringable::macro('classNamespace', function () {
        return new static(Str::classNamespace($this->value));
    });
}

if (! Stringable::hasMacro('excerpt')) {
    Stringable::macro('excerpt', function ($phrase = '', $options = []) {
        return Str::excerpt($this->value, $phrase, $options);
    });
}

if (! Stringable::hasMacro('headline')) {
    Stringable::macro('headline', function () {
        return new static(Str::headline($this->value));
    });
}

if (! Stringable::hasMacro('isAscii')) {
    Stringable::macro('isAscii', function () {
        return Str::isAscii($this->value);
    });
}

if (! Stringable::hasMacro('isUuid')) {
    Stringable::macro('isUuid', function () {
        return Str::isUuid($this->value);
    });
}

if (! Stringable::hasMacro('lcfirst')) {
    Stringable::macro('lcfirst', function () {
        return new static(Str::lcfirst($this->value));
    });
}

if (! Stringable::hasMacro('markdown')) {
    Stringable::macro('markdown', function (array $options = []) {
        return new static(Str::markdown($this->value, $options));
    });
}

if (! Stringable::hasMacro('newLine')) {
    Stringable::macro('newLine', function ($count = 1) {
        return $this->append(str_repeat(PHP_EOL, $count));
    });
}

if (! Stringable::hasMacro('reverse')) {
    Stringable::macro('reverse', function () {
        return new static(Str::reverse($this->value));
    });
}

if (! Stringable::hasMacro('squish')) {
    Stringable::macro('squish', function () {
        return new static(Str::squish($this->value));
    });
}

if (! Stringable::hasMacro('scan')) {
    Stringable::macro('scan', function ($format) {
        return collect(sscanf($this->value, $format));
    });
}

if (! Stringable::hasMacro('substrReplace')) {
    Stringable::macro('substrReplace', function ($replace, $offset = 0, $length = null) {
        return new static(Str::substrReplace($this->value, $replace, $offset, $length));
    });
}

if (! Stringable::hasMacro('swap')) {
    Stringable::macro('swap', function (array $map) {
        return new static(strtr($this->value, $map));
    });
}

if (! Stringable::hasMacro('test')) {
    Stringable::macro('test', function ($pattern) {
        /* @var Stringable $this */
        return $this->match($pattern)->isNotEmpty();
    });
}

if (! Stringable::hasMacro('toHtmlString')) {
    Stringable::macro('toHtmlString', function () {
        /* @var Stringable $this */
        return new HtmlString($this->value);
    });
}

if (! Stringable::hasMacro('ucsplit')) {
    Stringable::macro('ucsplit', function () {
        /* @var Stringable $this */
        return collect(Str::ucsplit($this->value));
    });
}

if (! Stringable::hasMacro('wrap')) {
    Stringable::macro('wrap', function ($before, $after = null) {
        return new static($before . $this->value . ($after ?: $before));
    });
}

if (! Stringable::hasMacro('whenContains')) {
    Stringable::macro('whenContains', function ($needles, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->contains($needles), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenContainsAll')) {
    Stringable::macro('whenContainsAll', function (array $needles, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->containsAll($needles), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenEndsWith')) {
    Stringable::macro('whenEndsWith', function ($needles, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->endsWith($needles), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenExactly')) {
    Stringable::macro('whenExactly', function ($needles, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->exactly($needles), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenIs')) {
    Stringable::macro('whenIs', function ($pattern, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->is($pattern), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenIsAscii')) {
    Stringable::macro('whenIsAscii', function ($callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->isAscii(), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenIsUuid')) {
    Stringable::macro('whenIsUuid', function ($callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->isUuid(), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenTest')) {
    Stringable::macro('whenTest', function ($pattern, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->test($pattern), $callback, $default);
    });
}

if (! Stringable::hasMacro('whenStartsWith')) {
    Stringable::macro('whenStartsWith', function ($needles, $callback, $default = null) {
        /* @var Stringable $this */
        return $this->when($this->startsWith($needles), $callback, $default);
    });
}

if (! Stringable::hasMacro('value')) {
    Stringable::macro('value', function () {
        /* @var Stringable $this */
        return $this->toString();
    });
}

if (! Stringable::hasMacro('toString')) {
    Stringable::macro('toString', function () {
        /* @var Stringable $this */
        return $this->value;
    });
}
