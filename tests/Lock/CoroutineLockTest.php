<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\CoroutineLock;

test('coroutine lock can be instantiated', function () {
    $lock = new CoroutineLock('test_lock', 60, 'owner123');

    expect($lock)->toBeInstanceOf(CoroutineLock::class);
    expect($lock->owner())->toBe('owner123');
});

test('coroutine lock uses prefix in constructor', function () {
    $lock = new CoroutineLock('test_lock', 60, 'owner123', ['prefix' => 'app:']);

    expect($lock)->toBeInstanceOf(CoroutineLock::class);
});

test('coroutine lock can force release', function () {
    $lock = new CoroutineLock('test_lock', 60, 'owner123');

    // Force release should not throw exception
    $lock->forceRelease();

    expect(true)->toBeTrue();
});

test('coroutine lock get current owner returns string', function () {
    $lock = new class('test_lock', 60, 'owner123') extends CoroutineLock {
        public function testGetCurrentOwner()
        {
            return $this->getCurrentOwner();
        }
    };

    $result = $lock->testGetCurrentOwner();

    expect($result)->toBeString();
});
