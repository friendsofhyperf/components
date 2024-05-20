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
     * @param callable|null $default
     * @return static
     */
    public function whenIsAscii($callback, $default = null)
    {
    }
}
