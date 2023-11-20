<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support;

use Hyperf\Conditionable\Conditionable;
use Hyperf\Macroable\Macroable;

class Numberable
{
    use Conditionable;
    use Macroable;

    /**
     * The underlying numeric value.
     *
     * @var int|float
     */
    protected $value;

    /**
     * Create a new instance of the class.
     *
     * @param int|float $value
     */
    public function __construct($value = 0)
    {
        $this->value = $value;
    }

    /**
     * Proxy dynamic properties onto methods.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->{$key}();
    }

    /**
     * Convert the current value to its string equivalent.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Get the raw numeric value.
     *
     * @return int|float
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Add the given value to the current value.
     *
     * @param int|float $value
     */
    public function add($value): static
    {
        return new static($this->value + $value);
    }

    /**
     * Subtract the given value from the current value.
     *
     * @param int|float $value
     */
    public function subtract($value): static
    {
        return new static($this->value - $value);
    }

    /**
     * Multiply the given value with the current value.
     *
     * @param int|float $value
     */
    public function multiply($value): static
    {
        return new static($this->value * $value);
    }

    /**
     * Divide the given value with the current value.
     *
     * @param int|float $value
     */
    public function divide($value): static
    {
        return new static($this->value / $value);
    }

    /**
     * Modulo the given value with the current value.
     *
     * @param int|float $value
     */
    public function modulo($value): static
    {
        return new static($this->value % $value);
    }

    /**
     * Raise the current value to the given exponent.
     *
     * @param int|float $value
     */
    public function pow($value): static
    {
        return new static($this->value ** $value);
    }

    /**
     * Format the current value according to the current locale.
     *
     * @param ?string $locale
     * @return string|false
     */
    public function format(?int $precision = null, ?int $maxPrecision = null, ?string $locale = null)
    {
        return Number::format($this->value, $precision, $maxPrecision, $locale);
    }

    /**
     * Spell out the current value in the given locale.
     *
     * @param ?string $locale
     * @return string
     */
    public function spell(?string $locale = null)
    {
        return Number::spell($this->value, $locale);
    }

    /**
     * Convert the current value to ordinal form.
     *
     * @param ?string $locale
     * @return string
     */
    public function ordinal(?string $locale = null)
    {
        return Number::ordinal($this->value, $locale);
    }

    /**
     * Convert the current value to its percentage equivalent.
     *
     * @param ?string $locale
     * @return string|false
     */
    public function percentage(int $precision = 0, ?int $maxPrecision = null, ?string $locale = null)
    {
        return Number::percentage($this->value, $precision, $maxPrecision, $locale);
    }

    /**
     * Convert the current value to its currency equivalent.
     *
     * @param ?string $locale
     * @return string|false
     */
    public function currency(string $in = 'USD', ?string $locale = null)
    {
        return Number::currency($this->value, $in, $locale);
    }

    /**
     * Convert the current value to its file size equivalent.
     *
     * @return string
     */
    public function fileSize(int $precision = 0, ?int $maxPrecision = null)
    {
        return Number::fileSize($this->value, $precision, $maxPrecision);
    }

    /**
     * Convert the current value to its human readable equivalent.
     *
     * @return string
     */
    public function forHumans(int $precision = 0, ?int $maxPrecision = null)
    {
        return Number::forHumans($this->value, $precision, $maxPrecision);
    }
}
