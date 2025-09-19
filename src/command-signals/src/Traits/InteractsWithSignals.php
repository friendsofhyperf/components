<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandSignals\Traits;

use FriendsOfHyperf\CommandSignals\SignalRegistry;

use function Hyperf\Coroutine\defer;
use function Hyperf\Support\make;

trait InteractsWithSignals
{
    protected ?SignalRegistry $signalRegistry = null;

    /**
     * Define a callback to be run when the given signal(s) occurs.
     *
     * @param int|int[] $signo
     * @param callable(int $signo): void $callback
     */
    protected function trap(array|int $signo, callable $callback): void
    {
        if (! $this->signalRegistry) {
            $this->signalRegistry = make(SignalRegistry::class);
            defer(fn () => $this->signalRegistry->unregister());
        }

        $this->signalRegistry->register($signo, $callback);
    }

    protected function untrap(null|array|int $signo = null): void
    {
        $this->signalRegistry?->unregister($signo);
    }
}
