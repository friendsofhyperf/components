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

use DateTimeImmutable;
use FriendsOfHyperf\Sentry\Util\PayloadLimiter;
use GuzzleHttp\Psr7\Utils;
use RuntimeException;

test('small values are kept as is', function () {
    $value = ['GET', 'key', 1, 1.5, true, null, 'nested' => ['a' => 'b']];

    expect(PayloadLimiter::limit($value))->toBe($value);
});

test('oversized values stay within the budget', function ($value) {
    $limited = PayloadLimiter::limit($value);

    expect(strlen(serialize($limited)))->toBeLessThan(PayloadLimiter::MAX_BYTES * 2);
})->with([
    'string' => [str_repeat('x', 1048576)],
    'strings' => [array_fill(0, 1000, str_repeat('x', 1024))],
    'integers' => [range(1, 100000)],
    'objects' => [array_fill(0, 100000, new RuntimeException('boom'))],
]);

test('truncated arrays report how many items were cut', function () {
    $limited = PayloadLimiter::limit(array_fill(0, 4, str_repeat('x', 4000)));

    expect($limited[0])->toBe(str_repeat('x', 4000))
        ->and(strlen($limited[2]))->toBeLessThan(4000)
        ->and($limited)->toHaveKey('… [truncated]', 1);
});

test('deep arrays are cut at the max depth', function () {
    expect(PayloadLimiter::limit([[[[[[1, 2]]]]]]))->toBe([[[[['[array(2)]']]]]]);
});

test('objects are never kept', function () {
    $stream = Utils::streamFor(str_repeat('x', 1048576));

    expect(PayloadLimiter::limit([$stream]))->toBe(['[object GuzzleHttp\Psr7\Stream]'])
        ->and(PayloadLimiter::limit(new RuntimeException('boom')))->toBe('RuntimeException: boom')
        ->and(PayloadLimiter::limit(new DateTimeImmutable('2026-01-02T03:04:05+00:00')))->toBe('2026-01-02T03:04:05+00:00');
});

test('guzzle options keep only scalar transfer settings', function () {
    $options = PayloadLimiter::guzzleOptions([
        'timeout' => 2.0,
        'http_errors' => false,
        'allow_redirects' => ['max' => 5],
        'body' => str_repeat('x', 1048576),
        'json' => ['secret' => 'value'],
        'auth' => ['user', 'password'],
        'handler' => fn () => null,
    ]);

    expect($options)->toBe(['timeout' => 2.0, 'http_errors' => false]);
});

test('guzzle options limit string settings', function () {
    $options = PayloadLimiter::guzzleOptions(['timeout' => 2.0, 'verify' => str_repeat('x', 1048576)]);

    expect($options['timeout'])->toBe(2.0)
        ->and(strlen($options['verify']))->toBeLessThan(PayloadLimiter::MAX_BYTES + 32);
});

test('an oversized key is not retained', function () {
    $limited = PayloadLimiter::limit(['small' => 'value', str_repeat('k', 1048576) => 'value', 'other' => 'value']);

    expect($limited)->toBe(['small' => 'value', '… [truncated]' => 2]);
});
