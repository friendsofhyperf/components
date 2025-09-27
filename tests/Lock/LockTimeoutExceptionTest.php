<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

test('lock timeout exception extends exception', function () {
    $exception = new LockTimeoutException();

    expect($exception)->toBeInstanceOf(Exception::class);
});

test('lock timeout exception can be created with message', function () {
    $exception = new LockTimeoutException('Lock timeout occurred');

    expect($exception->getMessage())->toBe('Lock timeout occurred');
});

test('lock timeout exception can be created with message and code', function () {
    $exception = new LockTimeoutException('Lock timeout occurred', 123);

    expect($exception->getMessage())->toBe('Lock timeout occurred');
    expect($exception->getCode())->toBe(123);
});

test('lock timeout exception can be thrown and caught', function () {
    expect(function () {
        throw new LockTimeoutException('Test timeout');
    })->toThrow(LockTimeoutException::class, 'Test timeout');
});
