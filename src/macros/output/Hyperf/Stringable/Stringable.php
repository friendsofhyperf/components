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
     * Find the multi-byte safe position of the first occurrence of the given substring.
     *
     * @param string $needle
     * @param int $offset
     * @param string|null $encoding
     * @return int|false
     */
    public function position($needle, $offset = 0, $encoding = null)
    {
    }

    /**
     * Take the first or last {$limit} characters.
     *
     * @return static
     */
    public function take(int $limit)
    {
    }

    /**
     * Convert the string into a `HtmlString` instance.
     *
     * @return \FriendsOfHyperf\Macros\Foundation\HtmlString
     * @deprecated
     */
    public function toHtmlString()
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
}
