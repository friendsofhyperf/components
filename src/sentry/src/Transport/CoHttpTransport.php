<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Transport;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\Serializer\PayloadSerializer;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;
use Throwable;

use function Hyperf\Support\msleep;

class CoHttpTransport implements TransportInterface
{
    protected ?Channel $chan = null;

    protected bool $workerExited = false;

    protected ?Coroutine $workerWatcher = null;

    protected ?Concurrent $concurrent = null;

    protected ?ClientBuilder $clientBuilder = null;

    protected int $channelSize = 65535;

    protected float $timeout = 0;

    public function __construct(
        protected ContainerInterface $container,
    ) {
        $config = $this->container->get(ConfigInterface::class);
        $channelSize = (int) $config->get('sentry.transport_channel_size', 65535);
        if ($channelSize > 0) {
            $this->channelSize = $channelSize;
        }

        $concurrentLimit = (int) $config->get('sentry.transport_concurrent_limit', 1000);
        if ($concurrentLimit > 0) {
            $this->concurrent = new Concurrent($concurrentLimit);
        }

        $this->timeout = $this->resolvePushTimeout();
    }

    public function send(Event $event): Result
    {
        $this->loop();

        if ($this->chan === null) {
            $this->logWarning('Sentry transport channel is not available, the event will be skipped.');

            return new Result(ResultStatus::skipped(), $event);
        }

        if (! $this->pushEvent($event)) {
            $this->logWarning('Sentry transport channel is full, the event will be skipped.');

            return new Result(ResultStatus::skipped(), $event);
        }

        return new Result(ResultStatus::success(), $event);
    }

    public function close(?int $timeout = null): Result
    {
        $timeout ??= 1;

        $chan = $this->chan;

        if ($chan === null) {
            return new Result(ResultStatus::success());
        }

        $startedAt = microtime(true);

        while (! $chan->isEmpty()) {
            if ((microtime(true) - $startedAt) >= $timeout) {
                break;
            }

            msleep(100);
        }

        $this->closeChannel();

        return new Result(ResultStatus::success());
    }

    /**
     * Resolve the timeout used when pushing an event into the channel from
     * the `sentry.transport_timeout` config.
     *
     * Swoole Channel::push() semantics: -1 (and any other non-positive
     * value) blocks until space is available, while a positive value waits
     * at most N seconds. We map a non-positive config to 0 so that
     * pushEvent() takes the non-blocking path and senders can never be
     * suspended indefinitely.
     */
    protected function resolvePushTimeout(): float
    {
        $timeout = (float) $this->container->get(ConfigInterface::class)->get('sentry.transport_timeout', 0);

        // A non-positive timeout means non-blocking: when the channel is full,
        // the push returns false immediately and the event is skipped.
        return $timeout <= 0 ? 0.0 : $timeout;
    }

    /**
     * Push an event into the channel.
     *
     * When the configured push timeout is not positive the push is
     * non-blocking: if the channel is full the event is skipped without
     * suspending the caller coroutine.
     */
    protected function pushEvent(Event $event): bool
    {
        $chan = $this->chan;

        if ($chan === null) {
            return false;
        }

        if ($this->timeout <= 0) {
            if ($chan->isFull()) {
                return false;
            }

            // Swoole Channel::push() treats a non-positive timeout as "block
            // until space is available", so guard against a full channel and
            // bound the push with a tiny timeout to cover the race where
            // another producer fills the channel right after the check.
            return $chan->push($event, 0.001);
        }

        return $chan->push($event, $this->timeout);
    }

    protected function loop(): void
    {
        if ($this->workerExited) {
            return;
        }

        if ($this->chan !== null) {
            return;
        }

        $this->chan = new Channel($this->channelSize);

        Coroutine::create(function () {
            try {
                while (true) {
                    $transport = $this->makeHttpTransport();
                    $logger = $this->clientBuilder?->getLogger();

                    while (true) {
                        /** @var null|Event|false $event */
                        $event = $this->chan?->pop();

                        if (! $event) {
                            break 2;
                        }

                        try {
                            $callable = static fn () => $transport->send($event);
                            if ($this->concurrent !== null) {
                                $this->concurrent->create($callable);
                            } else {
                                Coroutine::create($callable);
                            }
                        } catch (Throwable $e) {
                            $logger?->error('Failed to send event to Sentry: ' . $e->getMessage(), ['exception' => $e]);
                            $transport->close();

                            break;
                        } finally {
                            // Prevent memory leak
                            $event = null;
                        }
                    }
                }
            } catch (Throwable $e) {
                // The consumer died (e.g. makeHttpTransport() failed), close the
                // channel so that send() can rebuild it on the next call.
                $this->clientBuilder?->getLogger()?->error('Failed to initialize Sentry transport: ' . $e->getMessage(), ['exception' => $e]);
            } finally {
                $this->closeChannel();
            }
        });

        $this->workerWatcher ??= Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                // sleep before setting workerExited to prevent busy-waiting
                msleep(100);

                $this->workerExited = true;

                while (! $this->chan?->isEmpty()) {
                    msleep(100);
                }

                $this->closeChannel();
            }
        });
    }

    protected function makeHttpTransport(): TransportInterface
    {
        $this->clientBuilder ??= $this->container->get(ClientBuilder::class);

        return new \Sentry\Transport\HttpTransport(
            $this->clientBuilder->getOptions(),
            $this->clientBuilder->getHttpClient(),
            new PayloadSerializer($this->clientBuilder->getOptions()),
            $this->clientBuilder->getLogger()
        );
    }

    protected function closeChannel(): void
    {
        $chan = $this->chan;
        $chan?->close();

        if ($this->chan === $chan) {
            $this->chan = null;
        }
    }

    protected function logWarning(string $message): void
    {
        $logger = $this->clientBuilder?->getLogger();

        if ($logger === null) {
            try {
                $logger = $this->container->get(StdoutLoggerInterface::class);
            } catch (Throwable) {
                // Ignore, the logger is not available.
            }
        }

        $logger?->warning($message);
    }
}
