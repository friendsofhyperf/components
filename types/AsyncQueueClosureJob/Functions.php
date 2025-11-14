<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;
use function PHPStan\Testing\assertType;

// Basic dispatch() tests
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn () => 'result'));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(function () {
    return 'test';
}));

// Method chaining tests - setMaxAttempts()
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn () => true)->setMaxAttempts(3));

// Method chaining tests - onPool()
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn () => null)->onPool('default'));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn () => [])->onPool('custom-pool'));

// Method chaining tests - delay()
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn () => 123)->delay(10));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn () => new stdClass())->delay(0));

// Complex method chaining
assertType(
    'FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch',
    dispatch(fn () => 'multi-chain')
        ->setMaxAttempts(5)
        ->onPool('worker')
        ->delay(30)
);

assertType(
    'FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch',
    dispatch(function () {
        return ['key' => 'value'];
    })
        ->delay(60)
        ->setMaxAttempts(3)
);

// Different closure return types
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn (): string => 'typed return'));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn (): int => 42));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn (): array => []));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn (): bool => true));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn (): ?string => null));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(function (): void {
    // void return type
}));

// Closures with parameters
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn ($param) => $param));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(fn (string $name, int $age) => "{$name} is {$age}"));
assertType('FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch', dispatch(function ($a, $b) {
    return $a + $b;
}));
