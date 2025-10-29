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
     * Return the remainder of a string after the first occurrence of a given value.
     *
     * @param mixed $string
     * @return Stringable
     */
    public static function of($string)
    {
    }

    /**
     * Set the callable that will be used to generate UUIDs.
     */
    public static function createUuidsUsing(?callable $factory = null): void
    {
    }

    /**
     * Indicate that UUIDs should be created normally and not using a custom factory.
     */
    public static function createUuidsNormally(): void
    {
    }

    /**
     * Replace consecutive instances of a given character with a single character in the given string.
     *
     * @return string
     */
    public static function deduplicate(string $string, string $character = ' ')
    {
    }

    /**
     * Converts GitHub flavored Markdown into HTML.
     *
     * @param string $string
     * @return string
     */
    public static function markdown($string, array $options = [], array $extensions = [])
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
     * Transliterate a string to its closest ASCII representation.
     *
     * @param string $string
     * @param null|string $unknown
     * @param null|bool $strict
     * @return string
     */
    public static function transliterate($string, $unknown = '?', $strict = false)
    {
    }

    /**
     * Determine if a given string doesn't end with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function doesntEndWith($haystack, $needles)
    {
    }

    /**
     * Determine if a given string doesn't start with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function doesntStartWith($haystack, $needles)
    {
    }
}
