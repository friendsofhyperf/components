<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Lock\Driver;

use Hyperf\Coordinator\Timer;

class LoopRedisLock extends RedisLock
{
    protected static array $timerIds = [];

    protected static ?Timer $timer = null;

    public function acquire(): bool
    {
        $result = parent::acquire();
        if ($result) {
            self::$timer ??= new Timer();
            self::$timerIds[$this->name] = self::$timer->tick(10, fn () => $this->loopSet());
        }
        return $result;
    }

    public function release(): bool
    {
        self::$timer?->clear(self::$timerIds[$this->name] ?? 0);
        return parent::release();
    }

    public function forceRelease(): void
    {
        self::$timer?->clear(self::$timerIds[$this->name] ?? 0);
        parent::forceRelease();
    }

    protected function loopSet(): bool|string
    {
        if (! $this->store->exists($this->name)) {
            return Timer::STOP;
        }

        if ($this->seconds > 0) {
            return $this->store->set($this->name, $this->owner, ['EX' => $this->seconds]) == true;
        }
        return true;
    }
}
