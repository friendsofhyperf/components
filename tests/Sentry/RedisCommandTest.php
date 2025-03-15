<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FriendsOfHyperf\Sentry\Util\RedisCommand;

test('redis command with simple parameters', function () {
    $command = new RedisCommand('GET', ['key']);

    expect((string) $command)->toBe('GET key');
});

test('redis command with multiple simple parameters', function () {
    $command = new RedisCommand('MGET', ['key1', 'key2', 'key3']);

    expect((string) $command)->toBe('MGET key1 key2 key3');
});

test('redis command with array parameter', function () {
    $command = new RedisCommand('HMSET', ['user:1', ['name' => 'John', 'email' => 'john@example.com']]);

    expect((string) $command)->toBe('HMSET user:1 name John email john@example.com');
});

test('redis command with nested array parameter', function () {
    $command = new RedisCommand('HMSET', ['user:1', ['name' => 'John', 'data' => ['age' => 30, 'active' => true]]]);

    expect((string) $command)->toBe('HMSET user:1 name John data {"age":30,"active":true}');
});

test('redis command with indexed array parameter', function () {
    $command = new RedisCommand('LPUSH', ['list', ['item1', 'item2', 'item3']]);

    expect((string) $command)->toBe('LPUSH list item1 item2 item3');
});

test('redis command with empty parameters', function () {
    $command = new RedisCommand('PING');

    expect((string) $command)->toBe('PING ');
});

test('redis command with mixed parameters', function () {
    $command = new RedisCommand('ZADD', ['scores', 1, 'value1', 2, 'value2', ['extra' => 'data']]);

    expect((string) $command)->toBe('ZADD scores 1 value1 2 value2 extra data');
});

test('redis command with SET NX option', function () {
    $command = new RedisCommand('SET', ['key', 'value', 'NX']);

    expect((string) $command)->toBe('SET key value NX');
});

test('redis command with SET EX option', function () {
    $command = new RedisCommand('SET', ['key', 'value', 'EX', 60]);

    expect((string) $command)->toBe('SET key value EX 60');
});
