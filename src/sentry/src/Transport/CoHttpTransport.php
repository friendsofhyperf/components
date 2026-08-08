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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
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

    protected ?ClientBuilder $clientBuilder = null;

    protected ?TransportInterface $httpTransport = null;

    protected int $channelSize = 512;

    protected int $workerCount = 32;

    protected float $timeout = 0;

    protected int $inFlight = 0;

    protected int $accepted = 0;

    protected int $dropped = 0;

    public function __construct(
        protected ContainerInterface $container,
    ) {
        $config = $this->container->get(ConfigInterface::class);
        $channelSize = (int) $config->get('sentry.transport_channel_size', 512);
        if ($channelSize > 0) {
            $this->channelSize = $channelSize;
        }

        $concurrentLimit = (int) $config->get('sentry.transport_concurrent_limit', 32);
        if ($concurrentLimit > 0) {
            $this->workerCount = $concurrentLimit;
        }

        $timeout = (float) $config->get('sentry.transport_timeout', 0);
        if ($timeout >= 0) {
            $this->timeout = $timeout;
        }
    }

    public function send(Event $event): Result
    {
        if ($this->workerExited) {
            ++$this->dropped;

            return new Result(ResultStatus::skipped(), $event);
        }

        $this->loop();

        $chan = $this->chan;
        // Swoole treats a zero timeout as an infinite wait, so check capacity first
        // to provide fail-fast backpressure without suspending the application coroutine.
        if ($this->timeout === 0.0 && $chan !== null && $chan->getLength() >= $chan->getCapacity()) {
            ++$this->dropped;

            return new Result(ResultStatus::skipped(), $event);
        }

        if (! $chan?->push($event, $this->timeout)) {
            ++$this->dropped;

            return new Result(ResultStatus::skipped(), $event);
        }

        ++$this->accepted;

        return new Result(ResultStatus::success(), $event);
    }

    public function close(?int $timeout = null): Result
    {
        if ($timeout === null || $timeout <= 0) {
            return new Result(ResultStatus::success());
        }

        $deadline = microtime(true) + $timeout;
        while (! $this->isIdle()) {
            if (microtime(true) >= $deadline) {
                return new Result(ResultStatus::skipped());
            }

            msleep(1);
        }

        return new Result(ResultStatus::success());
    }

    /**
     * @return array{accepted: int, dropped: int, queued: int, in_flight: int, workers: int}
     */
    public function getStats(): array
    {
        return [
            'accepted' => $this->accepted,
            'dropped' => $this->dropped,
            'queued' => $this->chan?->getLength() ?? 0,
            'in_flight' => $this->inFlight,
            'workers' => $this->workerCount,
        ];
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

        for ($worker = 0; $worker < $this->workerCount; ++$worker) {
            Coroutine::create(fn () => $this->runWorker());
        }

        $this->watchWorkerExit();
    }

    protected function runWorker(): void
    {
        $chan = $this->chan;
        if ($chan === null) {
            return;
        }

        $transport = $this->getHttpTransport();
        $logger = $this->clientBuilder?->getLogger();

        while (true) {
            /** @var Event|false $event */
            $event = $chan->pop();
            if (! $event) {
                break;
            }

            ++$this->inFlight;

            try {
                $transport->send($event);
            } catch (Throwable $e) {
                $logger?->error('Failed to send event to Sentry: ' . $e->getMessage(), ['exception' => $e]);
            } finally {
                --$this->inFlight;
                $event = null;
            }
        }
    }

    protected function watchWorkerExit(): void
    {
        $this->workerWatcher ??= Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                $this->workerExited = true;

                while (! $this->isIdle()) {
                    msleep(100);
                }

                $this->closeChannel();
            }
        });
    }

    protected function getHttpTransport(): TransportInterface
    {
        return $this->httpTransport ??= $this->makeHttpTransport();
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

    protected function isIdle(): bool
    {
        return ($this->chan === null || $this->chan->isEmpty()) && $this->inFlight === 0;
    }
}
