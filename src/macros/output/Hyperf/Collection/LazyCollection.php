<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace Hyperf\Collection;

class LazyCollection
{
    /**
     * Create a new lazy collection instance.
     *
     * @param mixed $items
     * @return static
     */
    public static function make($items = [])
    {
    }

    /**
     * Determine if the collection contains a single element.
     * @return bool
     */
    public function isSingle()
    {
    }

    /**
     * Collapse the collection of items into a single array while preserving its keys.
     *
     * @return static<mixed, mixed>
     */
    public function collapseWithKeys()
    {
    }
}
