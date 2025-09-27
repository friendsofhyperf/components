<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Annotation\Blockable;
use FriendsOfHyperf\Lock\Annotation\Lock;

test('lock annotation can be created with required parameters', function () {
    $annotation = new Lock('test_lock');

    expect($annotation->name)->toBe('test_lock');
    expect($annotation->seconds)->toBe(0);
    expect($annotation->owner)->toBeNull();
    expect($annotation->driver)->toBe('default');
});

test('lock annotation can be created with all parameters', function () {
    $annotation = new Lock('test_lock', 60, 'owner123', 'redis');

    expect($annotation->name)->toBe('test_lock');
    expect($annotation->seconds)->toBe(60);
    expect($annotation->owner)->toBe('owner123');
    expect($annotation->driver)->toBe('redis');
});

test('blockable annotation can be created with default values', function () {
    $annotation = new Blockable();

    expect($annotation->prefix)->toBeNull();
    expect($annotation->value)->toBeNull();
    expect($annotation->seconds)->toBe(0);
    expect($annotation->ttl)->toBe(0);
    expect($annotation->driver)->toBe('default');
});

test('blockable annotation can be created with custom values', function () {
    $annotation = new Blockable('cache:', 'user_{id}', 30, 300, 'redis');

    expect($annotation->prefix)->toBe('cache:');
    expect($annotation->value)->toBe('user_{id}');
    expect($annotation->seconds)->toBe(30);
    expect($annotation->ttl)->toBe(300);
    expect($annotation->driver)->toBe('redis');
});

test('blockable annotation can be created with some parameters', function () {
    $annotation = new Blockable(seconds: 60, ttl: 120);

    expect($annotation->prefix)->toBeNull();
    expect($annotation->value)->toBeNull();
    expect($annotation->seconds)->toBe(60);
    expect($annotation->ttl)->toBe(120);
    expect($annotation->driver)->toBe('default');
});
