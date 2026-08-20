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

use FriendsOfHyperf\Sentry\Transport\CoHttpTransport;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Mockery;
use Psr\Container\ContainerInterface;
use Sentry\Event;
use Sentry\Transport\ResultStatus;
use Throwable;

use function Hyperf\Support\msleep;

// The shared vendor autoloader (symlinked to the main checkout) resolves
// FriendsOfHyperf\Sentry\* to the main checkout's source, so load the
// worktree's CoHttpTransport explicitly to test the local changes.
require_once dirname(__DIR__, 2) . '/src/sentry/src/Transport/CoHttpTransport.php';

class CoHttpTransportTestable extends CoHttpTransport
{
    public function resolvePushTimeoutForTest(): float
    {
        return $this->resolvePushTimeout();
    }

    public function getTimeoutForTest(): float
    {
        return $this->timeout;
    }

    public function pushEventForTest(Event $event): bool
    {
        return $this->pushEvent($event);
    }

    public function getChanForTest(): ?Channel
    {
        return $this->chan;
    }

    public function setChanForTest(?Channel $chan): void
    {
        $this->chan = $chan;
    }

    protected function loop(): void
    {
        // no-op: avoid spawning the consumer and worker watcher coroutines in tests
    }
}

function createCoHttpTransportTestable(array $overrides = []): CoHttpTransportTestable
{
    $config = new Config([
        'sentry' => array_merge([
            'transport_channel_size' => 512,
            'transport_concurrent_limit' => 100,
            'transport_timeout' => 0,
        ], $overrides),
    ]);

    $container = Mockery::mock(ContainerInterface::class);
    $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

    return new CoHttpTransportTestable($container);
}

function runTestInCoroutine(callable $callback): void
{
    $exception = null;

    \Swoole\Coroutine\run(function () use ($callback, &$exception) {
        try {
            $callback();
        } catch (Throwable $e) {
            $exception = $e;
        }
    });

    if ($exception !== null) {
        throw $exception;
    }
}

test('resolvePushTimeout returns non-blocking timeout for non-positive values', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable(['transport_timeout' => 0]);
        expect($transport->resolvePushTimeoutForTest())->toBe(0.0);

        $transport = createCoHttpTransportTestable(['transport_timeout' => -1]);
        expect($transport->resolvePushTimeoutForTest())->toBe(0.0);

        $transport = createCoHttpTransportTestable(['transport_timeout' => 1.5]);
        expect($transport->resolvePushTimeoutForTest())->toBe(1.5);
    });
});

test('constructor maps non-positive transport_timeout to a non-blocking push timeout', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable(['transport_timeout' => -1]);
        expect($transport->getTimeoutForTest())->toBe(0.0);

        $transport = createCoHttpTransportTestable(['transport_timeout' => 2]);
        expect($transport->getTimeoutForTest())->toBe(2.0);
    });
});

test('send returns skipped without blocking when the channel is full', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable([
            'transport_channel_size' => 1,
            'transport_timeout' => 0,
        ]);
        $transport->setChanForTest(new Channel(1));

        // Fill the channel first, the next push must not block and must fail.
        expect($transport->pushEventForTest(Event::createEvent()))->toBeTrue();

        $result = $transport->send(Event::createEvent());
        expect($result->getStatus())->toBe(ResultStatus::skipped());
    });
});

test('send returns skipped when the channel is missing', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable();
        $transport->setChanForTest(null);

        $result = $transport->send(Event::createEvent());
        expect($result->getStatus())->toBe(ResultStatus::skipped());
    });
});

test('send returns success when the channel has capacity', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable([
            'transport_channel_size' => 2,
            'transport_timeout' => 0,
        ]);
        $transport->setChanForTest(new Channel(2));

        $result = $transport->send(Event::createEvent());
        expect($result->getStatus())->toBe(ResultStatus::success());
    });
});

test('close waits for the channel to drain and then closes it', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable([
            'transport_channel_size' => 8,
            'transport_timeout' => 0,
        ]);
        $chan = new Channel(8);
        $transport->setChanForTest($chan);

        $chan->push(Event::createEvent());
        $chan->push(Event::createEvent());

        // Drain the channel slowly in a background coroutine.
        Coroutine::create(function () use ($chan) {
            while ($chan->pop(1) !== false) {
                msleep(50);
            }
        });

        $startedAt = microtime(true);
        $result = $transport->close(1);
        $elapsed = microtime(true) - $startedAt;

        expect($result->getStatus())->toBe(ResultStatus::success());
        // It waited for the backlog to be drained instead of closing immediately.
        expect($elapsed)->toBeGreaterThanOrEqual(0.04);
        // It did not block beyond the given timeout.
        expect($elapsed)->toBeLessThan(2.0);
        expect($transport->getChanForTest())->toBeNull();
    });
});

test('close returns success when the channel does not exist', function () {
    runTestInCoroutine(function () {
        $transport = createCoHttpTransportTestable();
        $transport->setChanForTest(null);

        $result = $transport->close(1);
        expect($result->getStatus())->toBe(ResultStatus::success());
    });
});
