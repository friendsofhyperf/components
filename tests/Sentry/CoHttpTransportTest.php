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
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Sentry\Event;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

final class SentryTransportContainer implements ContainerInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    public function get(string $id): mixed
    {
        if ($id === ConfigInterface::class) {
            return $this->config;
        }

        throw new RuntimeException("Unexpected container entry: {$id}");
    }

    public function has(string $id): bool
    {
        return $id === ConfigInterface::class;
    }
}

final class SlowTransport implements TransportInterface
{
    public int $completed = 0;

    public function send(Event $event): Result
    {
        \Swoole\Coroutine::sleep(0.05);
        ++$this->completed;

        return new Result(ResultStatus::success(), $event);
    }

    public function close(?int $timeout = null): Result
    {
        return new Result(ResultStatus::success());
    }
}

final class TestCoHttpTransport extends CoHttpTransport
{
    public function __construct(ContainerInterface $container, private TransportInterface $transport)
    {
        parent::__construct($container);
    }

    public function shutdown(): void
    {
        $this->workerExited = true;
        $this->closeChannel();
    }

    protected function watchWorkerExit(): void
    {
    }

    protected function makeHttpTransport(): TransportInterface
    {
        return $this->transport;
    }
}

/**
 * @internal
 */
final class CoHttpTransportTest extends TestCase
{
    public function testTransportBoundsQueuedEventsAndUsesFixedWorkers(): void
    {
        $config = new Config([
            'sentry' => [
                'transport_channel_size' => 2,
                'transport_concurrent_limit' => 1,
                'transport_timeout' => 0,
            ],
        ]);
        $container = new SentryTransportContainer($config);

        $slowTransport = new SlowTransport();
        $transport = new TestCoHttpTransport($container, $slowTransport);

        try {
            self::assertSame('SUCCESS', (string) $transport->send(Event::createEvent())->getStatus());
            \Swoole\Coroutine::sleep(0.005);

            self::assertSame(1, $transport->getStats()['in_flight']);
            self::assertSame('SUCCESS', (string) $transport->send(Event::createEvent())->getStatus());
            self::assertSame('SUCCESS', (string) $transport->send(Event::createEvent())->getStatus());
            self::assertSame('SKIPPED', (string) $transport->send(Event::createEvent())->getStatus());
            self::assertSame(
                [
                    'accepted' => 3,
                    'dropped' => 1,
                    'queued' => 2,
                    'in_flight' => 1,
                    'workers' => 1,
                ],
                $transport->getStats()
            );

            self::assertSame('SUCCESS', (string) $transport->close(1)->getStatus());
            self::assertSame(3, $slowTransport->completed);
            self::assertSame(0, $transport->getStats()['queued']);
            self::assertSame(0, $transport->getStats()['in_flight']);
        } finally {
            $transport->shutdown();
        }
    }

    public function testTransportDropsNewEventsAfterShutdown(): void
    {
        $config = new Config(['sentry' => []]);
        $container = new SentryTransportContainer($config);

        $transport = new TestCoHttpTransport($container, new SlowTransport());
        $transport->shutdown();

        self::assertSame('SKIPPED', (string) $transport->send(Event::createEvent())->getStatus());
        self::assertSame(1, $transport->getStats()['dropped']);
    }
}
