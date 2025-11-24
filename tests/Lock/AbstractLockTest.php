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

    private string $currentOwner = '';

    public function setCanAcquire(bool $canAcquire): void
    {
        $this->canAcquire = $canAcquire;
    }

    public function setCanRelease(bool $canRelease): void
    {
        $this->canRelease = $canRelease;
    }

    public function setCurrentOwner(string $owner): void
    {
        $this->currentOwner = $owner;
    }

    public function acquire(): bool
    {
        return $this->canAcquire;
    }

    public function release(): bool
    {
        return $this->canRelease;
    }

    public function forceRelease(): void
    {
        // Do nothing for test implementation
    }

    public function getSleepMilliseconds(): int
    {
        return $this->sleepMilliseconds;
    }

    protected function getCurrentOwner(): string
    {
        return $this->currentOwner;
    }

    protected function delayExpiration(): bool
    {
        return true;
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
