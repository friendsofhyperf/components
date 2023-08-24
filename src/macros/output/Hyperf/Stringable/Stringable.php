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

class Stringable
{
    /**
     * Get the namespace of the class path.
     *
     * @return static
     */
    public function classNamespace()
    {
    }

    /**
     * Extracts an excerpt from text that matches the first instance of a phrase.
     *
     * @param string $phrase
     * @param array $options
     * @return string|null
     */
    public function excerpt($phrase = '', $options = [])
    {
    }

    /**
     * Convert the given string to title case for each word.
     *
     * @return static
     */
    public function headline()
    {
    }

    /**
     * Determine if a given string is 7 bit ASCII.
     *
     * @return bool
     */
    public function isAscii()
    {
    }

    /**
     * Determine if a given string is valid JSON.
     *
     * @return bool
     */
    public function isJson()
    {
    }

    /**
     * Make a string's first character lowercase.
     *
     * @return static
     */
    public function lcfirst()
    {
    }

    /**
     * Convert GitHub flavored Markdown into HTML.
     *
     * @return static
     */
    public function markdown(array $options = [])
    {
    }

    /**
     * Convert inline Markdown into HTML.
     *
     * @return static
     */
    public function inlineMarkdown(array $options = [])
    {
    }

    /**
     * Append a new line to the string.
     *
     * @param int $count
     * @return $this
     */
    public function newLine($count = 1)
    {
    }

    /**
     * Replace the first occurrence of the given value if it appears at the start of the string.
     *
     * @param string $search
     * @param string $replace
     * @return static
     */
    public function replaceStart($search, $replace)
    {
    }

    /**
     * Replace the last occurrence of a given value if it appears at the end of the string.
     *
     * @param string $search
     * @param string $replace
     * @return static
     */
    public function replaceEnd($search, $replace)
    {
    }

    /**
     * Reverse the string.
     *
     * @return static
     */
    public function reverse()
    {
    }

    /**
     * Remove all "extra" blank space from the given string.
     *
     * @return static
     */
    public function squish()
    {
    }

    /**
     * Parse input from a string to a collection, according to a format.
     *
     * @param string $format
     * @return \Hyperf\Collection\Collection
     */
    public function scan($format)
    {
    }

    /**
     * Replace text within a portion of a string.
     *
     * @param array|string $replace
     * @param array|int $offset
     * @param array|int|null $length
     * @return static
     */
    public function substrReplace($replace, $offset = 0, $length = null)
    {
    }

    /**
     * Swap multiple keywords in a string with other keywords.
     *
     * @return static
     */
    public function swap(array $map)
    {
    }

    /**
     * Determine if the string matches the given pattern.
     *
     * @param string $pattern
     * @return bool
     */
    public function test($pattern)
    {
    }

    /**
     * Convert the string into a `HtmlString` instance.
     *
     * @return \FriendsOfHyperf\Support\HtmlString
     */
    public function toHtmlString()
    {
    }

    /**
     * Split a string by uppercase characters.
     *
     * @return \Hyperf\Collection\Collection
     */
    public function ucsplit()
    {
    }

    /**
     * Wrap the string with the given strings.
     *
     * @param string $before
     * @param string|null $after
     * @return static
     */
    public function wrap($before, $after = null)
    {
    }

    /**
     * Execute the given callback if the string contains a given substring.
     *
     * @param array|string $needles
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenContains($needles, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string contains all array values.
     *
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenContainsAll(array $needles, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string ends with a given substring.
     *
     * @param array|string $needles
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenEndsWith($needles, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string is an exact match with the given value.
     *
     * @param string $value
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenExactly($value, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string is not an exact match with the given value.
     *
     * @param string $value
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenNotExactly($value, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string matches a given pattern.
     *
     * @param array|string $pattern
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenIs($pattern, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string is 7 bit ASCII.
     *
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenIsAscii($callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string is a valid ULID.
     *
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenIsUlid($callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string is a valid UUID.
     *
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenIsUuid($callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string matches the given pattern.
     *
     * @param string $pattern
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenTest($pattern, $callback, $default = null)
    {
    }

    /**
     * Execute the given callback if the string starts with a given substring.
     *
     * @param array|string $needles
     * @param callable $callback
     * @param callable|null $default
     * @return static
     */
    public function whenStartsWith($needles, $callback, $default = null)
    {
    }

    /**
     * Get the underlying string value.
     *
     * @return string
     */
    public function value()
    {
    }

    /**
     * Get the underlying string value.
     *
     * @return string
     */
    public function toString()
    {
    }

    /**
     * Wrap a string to a given number of characters.
     *
     * @param int $characters
     * @param string $break
     * @param bool $cutLongWords
     * @return static
     */
    public function wordWrap($characters = 75, $break = "\n", $cutLongWords = false)
    {
    }
}
