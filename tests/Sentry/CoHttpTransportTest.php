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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Sentry\Event;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

function makeBlockedTransport(array $config, Channel $release): CoHttpTransport
{
    $container = m::mock(ContainerInterface::class);
    $container->allows('get')->with(ConfigInterface::class)->andReturn(new Config(['sentry' => $config]));

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
