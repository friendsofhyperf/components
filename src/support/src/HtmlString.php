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

use Hyperf\Contract\Htmlable;

class HtmlString implements Htmlable
{
    /**
     * Create a new HTML string instance.
     */
    public function __construct(protected string $html = '')
    {
    }

    /**
     * Get the HTML string.
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Get the HTML string.
     */
    public function toHtml(): string
    {
        return $this->html;
    }

    /**
     * Determine if the given HTML string is empty.
     */
    public function isEmpty(): bool
    {
        return $this->html === '';
    }

    /**
     * Determine if the given HTML string is not empty.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }
}
