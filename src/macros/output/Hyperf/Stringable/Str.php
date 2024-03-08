<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Hyperf\Stringable;

class Str
{
    /**
     * Convert the given string to APA-style title case.
     *
     * See: https://apastyle.apa.org/style-grammar-guidelines/capitalization/title-case
     *
     * @param string $value
     * @return string
     */
    public static function apa($value)
    {
    }

    /**
     * Set the callable that will be used to generate UUIDs.
     */
    public static function createUuidsUsing(callable $factory = null)
    {
    }

    /**
     * Indicate that UUIDs should be created normally and not using a custom factory.
     */
    public static function createUuidsNormally()
    {
    }

    /**
     * Convert the given string to title case for each word.
     *
     * @param string $value
     * @return string
     */
    public static function headline($value)
    {
    }

    /**
     * Determine if a given string is 7 bit ASCII.
     *
     * @param string $value
     * @return bool
     */
    public static function isAscii($value)
    {
    }

    /**
     * Converts GitHub flavored Markdown into HTML.
     *
     * @param string $string
     * @return string
     */
    public static function markdown($string, array $options = [])
    {
    }

    /**
     * Converts inline Markdown into HTML.
     *
     * @param string $string
     * @return string
     */
    public static function inlineMarkdown($string, array $options = [])
    {
    }

    /**
     * Find the multi-byte safe position of the first occurrence of a given substring in a string.
     *
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param string|null $encoding
     * @return int|false
     */
    public static function position($haystack, $needle, $offset = 0, $encoding = null)
    {
    }

    /**
     * Take the first or last {$limit} characters of a string.
     *
     * @param string $string
     * @return string
     */
    public static function take($string, int $limit)
    {
    }

    /**
     * Transliterate a string to its closest ASCII representation.
     *
     * @param string $string
     * @param string|null $unknown
     * @param bool|null $strict
     * @return string
     */
    public static function transliterate($string, $unknown = '?', $strict = false)
    {
    }
}
