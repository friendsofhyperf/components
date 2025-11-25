<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\LuaScripts;

test('release lock script returns valid lua script', function () {
    $script = LuaScripts::releaseLock();

    expect($script)->toBeString();
    expect($script)->toContain('redis.call("get",KEYS[1])');
    expect($script)->toContain('ARGV[1]');
    expect($script)->toContain('redis.call("del",KEYS[1])');
});

test('release lock script has correct logic structure', function () {
    $script = LuaScripts::releaseLock();

    // The script should check if the current owner matches
    expect($script)->toContain('if redis.call("get",KEYS[1]) == ARGV[1] then');
    // Should delete the key if owner matches
    expect($script)->toContain('return redis.call("del",KEYS[1])');
    // Should return 0 if owner doesn't match
    expect($script)->toContain('return 0');
    expect($script)->toContain('end');
});

test('refresh lock script returns valid lua script', function () {
    $script = LuaScripts::refreshLock();

    expect($script)->toBeString();
    expect($script)->toContain('redis.call("get",KEYS[1])');
    expect($script)->toContain('ARGV[1]');
    expect($script)->toContain('redis.call("expire",KEYS[1],ARGV[2])');
});

test('refresh lock script has correct logic structure', function () {
    $script = LuaScripts::refreshLock();

    // The script should check if the current owner matches
    expect($script)->toContain('if redis.call("get",KEYS[1]) == ARGV[1] then');
    // Should extend the expiration if owner matches
    expect($script)->toContain('return redis.call("expire",KEYS[1],ARGV[2])');
    // Should return 0 if owner doesn't match
    expect($script)->toContain('return 0');
    expect($script)->toContain('end');
});
