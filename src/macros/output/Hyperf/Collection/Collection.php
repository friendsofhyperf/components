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

class Collection
{
    /**
     * Create a new collection.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
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
