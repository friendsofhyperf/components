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

class BuilderMixin
{
    public function simpleFastPaginate()
    {
        return (new FastPaginate())->simpleFastPaginate();
    }

    public function fastPaginate()
    {
        return (new FastPaginate())->fastPaginate();
    }
}
