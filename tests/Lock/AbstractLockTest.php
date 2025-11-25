<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\AbstractLock;
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

// Create a concrete implementation for testing
class TestLock extends AbstractLock
{
    private bool $canAcquire = true;

    private bool $canRelease = true;

    private bool $canRefresh = true;

    private string $currentOwner = '';

    public function setCanAcquire(bool $canAcquire): void
    {
        $this->canAcquire = $canAcquire;
    }

    public function setCanRelease(bool $canRelease): void
    {
        $this->canRelease = $canRelease;
    }

    public function setCanRefresh(bool $canRefresh): void
    {
        $this->canRefresh = $canRefresh;
    }

    public function setCurrentOwner(string $owner): void
    {
        $this->currentOwner = $owner;
    }

    public function setAcquiredAt(?float $acquiredAt): void
    {
        $this->acquiredAt = $acquiredAt;
    }

    public function getAcquiredAt(): ?float
    {
        return $this->acquiredAt;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function acquire(): bool
    {
        if ($this->canAcquire) {
            $this->acquiredAt = microtime(true);
        }
        return $this->canAcquire;
    }

    public function release(): bool
    {
        if ($this->canRelease) {
            $this->acquiredAt = null;
        }
        return $this->canRelease;
    }

    public function forceRelease(): void
    {
        $this->acquiredAt = null;
    }

    public function refresh(?int $ttl = null): bool
    {
        if (! $this->canRefresh) {
            return false;
        }
        $ttl = $ttl ?? $this->seconds;
        if ($ttl <= 0) {
            return false;
        }
        $this->seconds = $ttl;
        $this->acquiredAt = microtime(true);
        return true;
    }

    public function getSleepMilliseconds(): int
    {
        return $this->sleepMilliseconds;
    }

    protected function getCurrentOwner(): string
    {
        return $this->currentOwner;
    }
}

test('abstract lock generates random owner when none provided', function () {
    $lock = new TestLock('test', 60);

    expect($lock->owner())->toBeString();
    expect(strlen($lock->owner()))->toBeGreaterThan(0);
});

test('abstract lock uses provided owner', function () {
    $lock = new TestLock('test', 60, 'custom_owner');

    expect($lock->owner())->toBe('custom_owner');
});

test('get method returns true when lock acquired without callback', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanAcquire(true);

    $result = $lock->get();

    expect($result)->toBeTrue();
});

test('get method returns false when lock cannot be acquired', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanAcquire(false);

    $result = $lock->get();

    expect($result)->toBeFalse();
});

test('get method executes callback when lock acquired', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanAcquire(true);
    $lock->setCanRelease(true);

    $callbackExecuted = false;

    $result = $lock->get(function () use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });

    expect($result)->toBe('callback_result');
    expect($callbackExecuted)->toBeTrue();
});

test('get method releases lock after callback execution', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanAcquire(true);
    $releaseCallCount = 0;

    // Override release to count calls
    $lock = new class('test', 60, 'owner') extends TestLock {
        public int $releaseCallCount = 0;

        public function release(): bool
        {
            ++$this->releaseCallCount;
            return true;
        }

        public function forceRelease(): void
        {
            // Do nothing for test
        }
    };
    $lock->setCanAcquire(true);

    $lock->get(function () {
        return 'result';
    });

    expect($lock->releaseCallCount)->toBe(1);
});

test('get method releases lock even when callback throws exception', function () {
    $lock = new TestLock('test', 60, 'owner');
    $releaseCallCount = 0;

    // Override release to count calls
    $lock = new class('test', 60, 'owner') extends TestLock {
        public int $releaseCallCount = 0;

        public function release(): bool
        {
            ++$this->releaseCallCount;
            return true;
        }

        public function forceRelease(): void
        {
            // Do nothing for test
        }
    };
    $lock->setCanAcquire(true);

    try {
        $lock->get(function () {
            throw new Exception('Test exception');
        });
    } catch (Exception $e) {
        // Expected
    }

    expect($lock->releaseCallCount)->toBe(1);
});

test('block method waits and acquires lock eventually', function () {
    $lock = new TestLock('test', 60, 'owner');
    $attemptCount = 0;

    // Override acquire to succeed on second attempt
    $lock = new class('test', 60, 'owner') extends TestLock {
        public int $attemptCount = 0;

        public function acquire(): bool
        {
            ++$this->attemptCount;
            return $this->attemptCount >= 2; // Succeed on second attempt
        }

        public function forceRelease(): void
        {
            // Do nothing for test
        }
    };

    // Use a very short sleep time for testing
    $lock->betweenBlockedAttemptsSleepFor(1);

    $result = $lock->block(1); // 1 second timeout

    expect($result)->toBeTrue();
    expect($lock->attemptCount)->toBeGreaterThan(1);
});

test('block method throws timeout exception when lock cannot be acquired', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanAcquire(false);
    $lock->betweenBlockedAttemptsSleepFor(1); // Very short sleep for test

    expect(fn () => $lock->block(0)) // Very short timeout
        ->toThrow(LockTimeoutException::class);
});

test('block method executes callback when lock acquired', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanAcquire(true);
    $lock->setCanRelease(true);

    $callbackExecuted = false;

    $result = $lock->block(1, function () use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });

    expect($result)->toBe('callback_result');
    expect($callbackExecuted)->toBeTrue();
});

test('is owned by returns true for matching owner', function () {
    $lock = new TestLock('test', 60, 'owner123');
    $lock->setCurrentOwner('owner123');

    expect($lock->isOwnedBy('owner123'))->toBeTrue();
});

test('is owned by returns false for different owner', function () {
    $lock = new TestLock('test', 60, 'owner123');
    $lock->setCurrentOwner('different_owner');

    expect($lock->isOwnedBy('owner123'))->toBeFalse();
});

test('between blocked attempts sleep for sets sleep milliseconds', function () {
    $lock = new TestLock('test', 60, 'owner');

    $result = $lock->betweenBlockedAttemptsSleepFor(500);

    expect($result)->toBe($lock);
    expect($lock->getSleepMilliseconds())->toBe(500);
});

test('refresh method returns true when lock can be refreshed', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanRefresh(true);
    $lock->acquire();

    $result = $lock->refresh();

    expect($result)->toBeTrue();
});

test('refresh method returns false when ttl is zero or negative', function () {
    $lock = new TestLock('test', 0, 'owner');
    $lock->setCanRefresh(true);

    $result = $lock->refresh();

    expect($result)->toBeFalse();
});

test('refresh method updates seconds when new ttl provided', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanRefresh(true);
    $lock->acquire();

    $lock->refresh(120);

    expect($lock->getSeconds())->toBe(120);
});

test('refresh method updates acquired at timestamp', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setCanRefresh(true);
    $lock->acquire();

    $originalAcquiredAt = $lock->getAcquiredAt();
    usleep(1000); // Sleep 1ms
    $lock->refresh();

    expect($lock->getAcquiredAt())->toBeGreaterThan($originalAcquiredAt);
});

test('isExpired returns false when lock has no expiration', function () {
    $lock = new TestLock('test', 0, 'owner');
    $lock->acquire();

    expect($lock->isExpired())->toBeFalse();
});

test('isExpired returns true when acquiredAt is null', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setAcquiredAt(null);

    expect($lock->isExpired())->toBeTrue();
});

test('isExpired returns false when lock is still valid', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->acquire();

    expect($lock->isExpired())->toBeFalse();
});

test('isExpired returns true when lock has expired', function () {
    $lock = new TestLock('test', 1, 'owner');
    $lock->setAcquiredAt(microtime(true) - 2); // Acquired 2 seconds ago

    expect($lock->isExpired())->toBeTrue();
});

test('getRemainingLifetime returns null when lock has no expiration', function () {
    $lock = new TestLock('test', 0, 'owner');
    $lock->acquire();

    expect($lock->getRemainingLifetime())->toBeNull();
});

test('getRemainingLifetime returns null when acquiredAt is null', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->setAcquiredAt(null);

    expect($lock->getRemainingLifetime())->toBeNull();
});

test('getRemainingLifetime returns positive value for valid lock', function () {
    $lock = new TestLock('test', 60, 'owner');
    $lock->acquire();

    $remaining = $lock->getRemainingLifetime();

    expect($remaining)->toBeFloat();
    expect($remaining)->toBeGreaterThan(0);
    expect($remaining)->toBeLessThanOrEqual(60);
});

test('getRemainingLifetime returns zero when lock has expired', function () {
    $lock = new TestLock('test', 1, 'owner');
    $lock->setAcquiredAt(microtime(true) - 2); // Acquired 2 seconds ago

    expect($lock->getRemainingLifetime())->toBe(0.0);
});
