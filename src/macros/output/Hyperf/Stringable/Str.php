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
     * Get the smallest possible portion of a string between two given values.
     *
     * @param string $subject
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function betweenFirst($subject, $from, $to)
    {
    }

    /**
     * Get the namespace of the class path.
     *
     * @param string $value
     * @return string
     */
    public static function classNamespace($value)
    {
    }

    /**
     * Extracts an excerpt from text that matches the first instance of a phrase.
     *
     * @param string $text
     * @param string $phrase
     * @param array $options
     * @return string|null
     */
    public static function excerpt($text, $phrase = '', $options = [])
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
     * Determine if a given string is valid JSON.
     *
     * @param string $value
     * @return bool
     */
    public static function isJson($value)
    {
    }

    /**
     * Make a string's first character lowercase.
     *
     * @param string $string
     * @return string
     */
    public static function lcfirst($string)
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
     * Generate a random, secure password.
     *
     * @param int $length
     * @param bool $letters
     * @param bool $numbers
     * @param bool $symbols
     * @param bool $spaces
     * @return string
     */
    public static function password($length = 32, $letters = true, $numbers = true, $symbols = true, $spaces = false)
    {
    }

    /**
     * Replace the first occurrence of the given value if it appears at the start of the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceStart($search, $replace, $subject)
    {
    }

    /**
     * Replace the last occurrence of a given value if it appears at the end of the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceEnd($search, $replace, $subject)
    {
    }

    /**
     * Reverse the given string.
     *
     * @return string
     */
    public static function reverse(string $value)
    {
    }

    /**
     * Remove all "extra" blank space from the given string.
     *
     * @param string $value
     * @return string
     */
    public static function squish($value)
    {
    }

    /**
     * Replace text within a portion of a string.
     *
     * @param array|string $string
     * @param array|string $replace
     * @param array|int $offset
     * @param array|int|null $length
     * @return array|string
     */
    public static function substrReplace($string, $replace, $offset = 0, $length = null)
    {
    }

    /**
     * Swap multiple keywords in a string with other keywords.
     *
     * @param string $subject
     * @return string
     */
    public static function swap(array $map, $subject)
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
     * Split a string into pieces by uppercase characters.
     *
     * @param string $string
     * @return array
     */
    public static function ucsplit($string)
    {
    }

    /**
     * Get the number of words a string contains.
     *
     * @param string $string
     * @return int
     */
    public static function wordCount($string)
    {
    }

    /**
     * Wrap the string with the given strings.
     *
     * @param string $before
     * @param string|null $after
     * @param mixed $value
     * @return string
     */
    public static function wrap($value, $before, $after = null)
    {
    }

    /**
     * Wrap a string to a given number of characters.
     *
     * @param string $string
     * @param int $characters
     * @param string $break
     * @param bool $cutLongWords
     * @return string
     */
    public static function wordWrap($string, $characters = 75, $break = "\n", $cutLongWords = false)
    {
    }
}
