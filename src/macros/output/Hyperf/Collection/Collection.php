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
     * Get an item from the collection by key or add it to collection if it does not exist.
     *
     * @template TGetOrPutValue
     *
     * @param int|string $key
     * @param TGetOrPutValue $value
     * @return TGetOrPutValue
     */
    public function getOrPut($key, $value)
    {
    }

    /**
     * Determine if the collection contains a single element.
     *
     * @return bool
     */
    public function isSingle()
    {
    }
}
