<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\FastPaginate;

/**
 * @mixin \Hyperf\Scout\Builder
 */
class ScoutMixin
{
    public function fastPaginate($perPage = null, $pageName = 'page', $page = null)
    {
        // Just defer to the Scout Builder for DX purposes.
        return fn ($perPage = null, $pageName = 'page', $page = null) => $this->paginate($perPage, $pageName, $page);
    }
}
