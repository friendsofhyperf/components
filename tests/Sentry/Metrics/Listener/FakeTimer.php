<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry\Metrics\Listener;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\Timer;

/**
 * A Timer that records tick() calls without spawning coroutines.
 *
 * @internal
 */
class FakeTimer extends Timer
{
    /**
     * @var array<int, array{0: float, 1: callable, 2: string}>
     */
    public array $ticks = [];

    public function tick(float $timeout, callable $closure, string $identifier = Constants::WORKER_EXIT): int
    {
        $this->ticks[] = [$timeout, $closure, $identifier];

        return count($this->ticks);
    }
}
