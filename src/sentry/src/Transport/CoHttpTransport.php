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
    }

    public function send(Event $event): Result
    {
        $this->loop();

        $chan = $this->chan;
        $chan?->push($event);

        return new Result(ResultStatus::success(), $event);
    }

    public function close(?int $timeout = null): Result
    {
        return new Result(ResultStatus::success());
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

            $this->closeChannel();
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
}
