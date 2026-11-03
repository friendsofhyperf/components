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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Sentry\Event;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

function makeBlockedTransport(array $config, Channel $release, ?StdoutLoggerInterface $logger = null): CoHttpTransport
{
    $container = m::mock(ContainerInterface::class);
    $container->allows('get')->with(ConfigInterface::class)->andReturn(new Config(['sentry' => $config]));
    $container->allows('has')->with(StdoutLoggerInterface::class)->andReturn($logger !== null);
    $logger && $container->allows('get')->with(StdoutLoggerInterface::class)->andReturn($logger);

    $inner = new class($release) implements TransportInterface {
        public function __construct(private Channel $release)
        {
        }

        public function send(Event $event): Result
        {
            $this->release->pop();

            return new Result(ResultStatus::failed());
        }

        public function close(?int $timeout = null): Result
        {
            return new Result(ResultStatus::success());
        }
    };

    return new class($container, $inner) extends CoHttpTransport {
        public function __construct(ContainerInterface $container, private TransportInterface $inner)
        {
            parent::__construct($container);
        }

        protected function makeHttpTransport(): TransportInterface
        {
            return $this->inner;
        }
    };
}

function sendEvents(CoHttpTransport $transport, int $count): array
{
    $statuses = [];
    for ($i = 0; $i < $count; ++$i) {
        $statuses[] = (string) $transport->send(Event::createEvent())->getStatus();
    }

    return $statuses;
}

beforeEach(function () {
    $this->release = new Channel(1);
});

afterEach(function () {
    $this->release->close();
    CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    CoordinatorManager::clear(Constants::WORKER_EXIT);
});

// With a concurrent limit of 1 the channel holds its capacity on top of one event being sent and one waiting for the limit.
test('a full channel drops events without blocking by default', function () {
    $transport = makeBlockedTransport(['transport_channel_size' => 2, 'transport_concurrent_limit' => 1], $this->release);

    expect(sendEvents($transport, 6))->toBe(['SUCCESS', 'SUCCESS', 'SUCCESS', 'SUCCESS', 'SKIPPED', 'SKIPPED']);
});

test('dropped events are merged into one warning per interval', function () {
    $messages = [];
    $logger = m::mock(StdoutLoggerInterface::class);
    $logger->allows('warning')->andReturnUsing(function (string $message) use (&$messages) {
        $messages[] = $message;
    });

    $transport = makeBlockedTransport(['transport_channel_size' => 1, 'transport_concurrent_limit' => 1], $this->release, $logger);

    // The first drop warns right away, the next two wait for the interval to end.
    expect(sendEvents($transport, 6))->toBe(['SUCCESS', 'SUCCESS', 'SUCCESS', 'SKIPPED', 'SKIPPED', 'SKIPPED'])
        ->and($messages)->toHaveCount(1)
        ->and($messages[0])->toContain('dropped 1 event(s)');

    (fn () => $this->lastDropWarningAt = 0.0)->call($transport);
    sendEvents($transport, 1);

    expect($messages)->toHaveCount(2)
        ->and($messages[1])->toContain('dropped 3 event(s)');
});

test('a stuck warning holds neither the caller nor more than one coroutine', function () {
    $written = 0;
    $stuck = new Channel(1);
    $logger = m::mock(StdoutLoggerInterface::class);
    $logger->allows('warning')->andReturnUsing(function () use (&$written, $stuck) {
        ++$written;
        $stuck->pop();
    });

    $transport = makeBlockedTransport(['transport_channel_size' => 1, 'transport_concurrent_limit' => 1], $this->release, $logger);

    expect(sendEvents($transport, 4))->toBe(['SUCCESS', 'SUCCESS', 'SUCCESS', 'SKIPPED'])
        ->and($written)->toBe(1);

    (fn () => $this->lastDropWarningAt = 0.0)->call($transport);

    expect(sendEvents($transport, 1))->toBe(['SKIPPED'])
        ->and($written)->toBe(1);

    $stuck->close();
});

test('legacy blocking timeouts are treated as drop on full', function ($timeout) {
    $transport = makeBlockedTransport([
        'transport_channel_size' => 1,
        'transport_concurrent_limit' => 1,
        'transport_timeout' => $timeout,
    ], $this->release);

    expect(sendEvents($transport, 4))->toBe(['SUCCESS', 'SUCCESS', 'SUCCESS', 'SKIPPED']);
})->with([[0], [-1]]);

test('a positive timeout waits for a bounded time', function () {
    $transport = makeBlockedTransport([
        'transport_channel_size' => 1,
        'transport_concurrent_limit' => 1,
        'transport_timeout' => 0.01,
    ], $this->release);

    $start = microtime(true);
    $statuses = sendEvents($transport, 4);

    expect($statuses)->toBe(['SUCCESS', 'SUCCESS', 'SUCCESS', 'SKIPPED'])
        ->and(microtime(true) - $start)->toBeGreaterThanOrEqual(0.01);
});
