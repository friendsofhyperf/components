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
     * Encrypt the string.
     *
     * @return static
     */
    public function encrypt(bool $serialize = false)
    {
    }

    /**
     * Decrypt the string.
     *
     * @return static
     */
    public function decrypt(bool $serialize = false)
    {
    }

    /**
     * Replace consecutive instances of a given character with a single character.
     *
     * @return static
     */
    public function deduplicate(string $character = ' ')
    {
    }

    /**
     * Hash the string using the given algorithm.
     *
     * @return static
     */
    public function hash(string $algorithm)
    {
    }

    /**
     * Convert GitHub flavored Markdown into HTML.
     *
     * @return static
     */
    public function markdown(array $options = [], array $extensions = [])
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
     * Convert the string into a `HtmlString` instance.
     *
     * @return \FriendsOfHyperf\Support\HtmlString
     */
    public function toHtmlString()
    {
    }

    /**
     * Execute the given callback if the string is 7 bit ASCII.
     *
     * @param callable $callback
     * @param null|callable $default
     * @return static
     */
    public function whenIsAscii($callback, $default = null)
    {
    }
}
