<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Bus;

use Hyperf\Conditionable\Conditionable;

abstract class AbstractPendingDispatch
{
    use Conditionable;

    private bool $defer = false;

    public function __destruct()
    {
        if ($this->defer) {
            return;
        }

        $this->dispatch();
    }

    final public function defer(): void
    {
        $this->defer = true;

        defer(fn () => $this->dispatch());
    }

    abstract protected function dispatch();
}
