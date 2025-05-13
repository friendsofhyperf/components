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

use Hyperf\Macroable\Macroable;
use NumberFormatter;
use RuntimeException;

use function Hyperf\Tappable\tap;

class Number
{
    use Macroable;

    /**
     * The current default locale.
     *
     * @var string
     */
    protected static $locale = 'en';

    /**
     * Format the given number according to the current locale.
     *
     * @return string|false
     */
    public static function format(int|float $number, ?int $precision = null, ?int $maxPrecision = null, ?string $locale = null)
    {
        static::ensureIntlExtensionIsInstalled();

        $formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::DECIMAL);

        if (! is_null($maxPrecision)) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision);
        } elseif (! is_null($precision)) {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
        }

        return $formatter->format($number);
    }

    /**
     * Spell out the given number in the given locale.
     *
     * @return string
     */
    public static function spell(int|float $number, ?string $locale = null, ?int $after = null, ?int $until = null)
    {
        static::ensureIntlExtensionIsInstalled();

        if (! is_null($after) && $number <= $after) {
            return static::format($number, locale: $locale);
        }

        if (! is_null($until) && $number >= $until) {
            return static::format($number, locale: $locale);
        }

        $formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::SPELLOUT);

        return $formatter->format($number);
    }

    /**
     * Convert the given number to ordinal form.
     *
     * @return string
     */
    public static function ordinal(int|float $number, ?string $locale = null)
    {
        static::ensureIntlExtensionIsInstalled();

        $formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::ORDINAL);

        return $formatter->format($number);
    }

    /**
     * Convert the given number to its percentage equivalent.
     *
     * @return string|false
     */
    public static function percentage(int|float $number, int $precision = 0, ?int $maxPrecision = null, ?string $locale = null)
    {
        static::ensureIntlExtensionIsInstalled();

        $formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::PERCENT);

        if (! is_null($maxPrecision)) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision);
        } else {
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);
        }

        return $formatter->format($number / 100);
    }

    /**
     * Convert the given number to its currency equivalent.
     *
     * @return string|false
     */
    public static function currency(int|float $number, string $in = 'USD', ?string $locale = null)
    {
        static::ensureIntlExtensionIsInstalled();

        $formatter = new NumberFormatter($locale ?? static::$locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($number, $in);
    }

    /**
     * Convert the given number to its file size equivalent.
     *
     * @return string
     */
    public static function fileSize(int|float $bytes, int $precision = 0, ?int $maxPrecision = null, bool $useBinaryPrefix = false)
    {
        $base = $useBinaryPrefix ? 1024 : 1000;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $units = $useBinaryPrefix
            ? ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB', 'RiB', 'QiB']
            : ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'RB', 'QB'];

        for ($i = 0; ($bytes / $base) > 0.9 && ($i < count($units) - 1); ++$i) {
            $bytes /= $base;
        }

        return sprintf('%s %s', static::format($bytes, $precision, $maxPrecision), $units[$i]);
    }

    /**
     * Convert the number to its human-readable equivalent.
     *
     * @return bool|string
     */
    public static function abbreviate(int|float $number, int $precision = 0, ?int $maxPrecision = null)
    {
        return static::forHumans($number, $precision, $maxPrecision, abbreviate: true);
    }

    /**
     * Convert the number to its human-readable equivalent.
     *
     * @return bool|string
     */
    public static function forHumans(int|float $number, int $precision = 0, ?int $maxPrecision = null, bool $abbreviate = false)
    {
        return static::summarize($number, $precision, $maxPrecision, $abbreviate ? [
            3 => 'K',
            6 => 'M',
            9 => 'B',
            12 => 'T',
            15 => 'Q',
        ] : [
            3 => ' thousand',
            6 => ' million',
            9 => ' billion',
            12 => ' trillion',
            15 => ' quadrillion',
        ]);
    }

    /**
     * Clamp the given number between the given minimum and maximum.
     *
     * @return int|float
     */
    public static function clamp(int|float $number, int|float $min, int|float $max)
    {
        return min(max($number, $min), $max);
    }

    /**
     * Split the given number into pairs of min/max values.
     *
     * @return array
     */
    public static function pairs(int|float $to, int|float $by, int|float $offset = 1)
    {
        $output = [];

        for ($lower = 0; $lower < $to; $lower += $by) {
            $upper = $lower + $by;

            if ($upper > $to) {
                $upper = $to;
            }

            $output[] = [$lower + $offset, $upper];
        }

        return $output;
    }

    /**
     * Execute the given callback using the given locale.
     *
     * @return mixed
     */
    public static function withLocale(string $locale, callable $callback)
    {
        $previousLocale = static::$locale;

        static::useLocale($locale);

        return tap($callback(), fn () => static::useLocale($previousLocale));
    }

    /**
     * Set the default locale.
     */
    public static function useLocale(string $locale)
    {
        static::$locale = $locale;
    }

    /**
     * Convert the number to its human-readable equivalent.
     *
     * @return string|false
     */
    protected static function summarize(int|float $number, int $precision = 0, ?int $maxPrecision = null, array $units = [])
    {
        if (empty($units)) {
            $units = [
                3 => 'K',
                6 => 'M',
                9 => 'B',
                12 => 'T',
                15 => 'Q',
            ];
        }

        switch (true) {
            case floatval($number) === 0.0:
                return $precision > 0 ? static::format(0, $precision, $maxPrecision) : '0';
            case $number < 0:
                return sprintf('-%s', static::summarize(abs($number), $precision, $maxPrecision, $units));
            case $number >= 1e15:
                return sprintf('%s' . end($units), static::summarize($number / 1e15, $precision, $maxPrecision, $units));
        }

        $numberExponent = floor(log10($number));
        $displayExponent = $numberExponent - ($numberExponent % 3);
        $number /= pow(10, $displayExponent);

        return trim(sprintf('%s%s', static::format($number, $precision, $maxPrecision), $units[$displayExponent] ?? ''));
    }

    /**
     * Ensure the "intl" PHP extension is installed.
     */
    protected static function ensureIntlExtensionIsInstalled()
    {
        if (! extension_loaded('intl')) {
            $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];

            throw new RuntimeException('The "intl" PHP extension is required to use the [' . $method . '] method.');
        }
    }
}
