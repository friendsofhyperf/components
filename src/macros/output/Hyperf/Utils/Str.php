<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Hyperf\Utils;

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
     * @return null|string
     */
    public static function excerpt($text, $phrase = '', $options = [])
    {
    }

    /**
     * Remove all strings from the casing caches.
     */
    public static function flushCache()
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
     * Determine if a given string is a valid UUID.
     *
     * @param string $value
     * @return bool
     */
    public static function isUuid($value)
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
     * Generate a time-ordered UUID (version 4).
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public static function orderedUuid()
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
     * @param null|array|int $length
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
     * @param null|string $unknown
     * @param null|bool $strict
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
     * Generate a UUID (version 4).
     *
     * @return \Ramsey\Uuid\UuidInterface
     */
    public static function uuid()
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
}
