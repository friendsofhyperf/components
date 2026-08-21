<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry\Metrics;

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Metrics\CoroutineServerStats;
use FriendsOfHyperf\Sentry\Metrics\Listener\RequestWatcher;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpServer\Event\RequestReceived;
use Mockery as m;

function waitForCoroutine(int $cid): void
{
    if (\Swoole\Coroutine::getCid() === -1) {
        return; // Top level: the created coroutine already finished synchronously.
    }

    while (\Swoole\Coroutine::exists($cid)) {
        \Swoole\Coroutine::sleep(0.001);
    }
}

test('process request received increments counters without throwing', function () {
    $stats = new CoroutineServerStats();
    $feature = m::mock(Feature::class);
    $feature->shouldReceive('isMetricsEnabled')->andReturn(true);

    $watcher = new RequestWatcher($stats, $feature);
    $request = new Request('GET', 'http://127.0.0.1:9501/health');

    $snapshot = null;
    $cid = \Swoole\Coroutine::create(function () use ($watcher, $request, $stats, &$snapshot) {
        $watcher->process(new RequestReceived($request, null));
        $snapshot = [
            'accept_count' => $stats->accept_count,
            'request_count' => $stats->request_count,
            'connection_num' => $stats->connection_num,
        ];
    });

    waitForCoroutine($cid);

    expect(\Swoole\Coroutine::exists($cid))->toBeFalse()
        ->and($snapshot['accept_count'])->toBe(1)
        ->and($snapshot['request_count'])->toBe(1)
        ->and($snapshot['connection_num'])->toBe(1);
});

test('defer closes the request counters after the coroutine ends', function () {
    $stats = new CoroutineServerStats();
    $feature = m::mock(Feature::class);
    $feature->shouldReceive('isMetricsEnabled')->andReturn(true);

    $watcher = new RequestWatcher($stats, $feature);
    $request = new Request('GET', 'http://127.0.0.1:9501/health');

    $cid = \Swoole\Coroutine::create(function () use ($watcher, $request) {
        $watcher->process(new RequestReceived($request, null));
    });

    waitForCoroutine($cid);

    expect($stats->close_count)->toBe(1)
        ->and($stats->response_count)->toBe(1)
        ->and($stats->connection_num)->toBe(0);
});
