<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\HttpClient;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Sentry\HttpClient\Request;
use Sentry\HttpClient\Response;
use Sentry\Options;
use Throwable;

use function Hyperf\Tappable\tap;

class HttpClient extends \Sentry\HttpClient\HttpClient
{
    protected ?Channel $chan = null;

    protected ?Concurrent $concurrent = null;

    protected ?Coroutine $waitingWorkerExit = null;

    protected bool $workerExited = false;

    public function __construct(
        string $sdkIdentifier,
        string $sdkVersion,
        protected int $channelSize = 65535,
        int $concurrentLimit = 100,
    ) {
        parent::__construct($sdkIdentifier, $sdkVersion);

        if ($concurrentLimit > 0) {
            $this->concurrent = new Concurrent($concurrentLimit);
        }
    }

    public function sendRequest(Request $request, Options $options): Response
    {
        // Start the loop if not started yet
        $this->loop();

        // Push the request to the channel
        $chan = $this->chan;
        $chan?->push([$request, $options]);

        return new Response(202, ['X-Sentry-Request-Status' => ['Queued']], '');
    }

    protected function loop(): void
    {
        // The worker already exited
        if ($this->workerExited) {
            return;
        }

        // Initialize the channel and start the loop
        $this->chan ??= tap(new Channel($this->channelSize), function () {
            // Dump memory usage and channel size
            // Coroutine::create(function () {
            //     while (! $this->chan?->isClosing()) {
            //         dump('Memory Usage(MB): ' . memory_get_usage(true) / 1024 / 1024);
            //         dump('Channel Size: ' . $this->chan?->getLength());
            //         sleep(1);
            //     }
            // });

            // Start the loop
            Coroutine::create(function () {
                try {
                    while (true) {
                        while (true) {
                            // If the channel is closing or pop failed, exit the loop
                            if (! $args = $this->chan?->pop()) {
                                break 2;
                            }
                            try {
                                $callable = fn () => parent::sendRequest(...$args);
                                if ($this->concurrent) {
                                    $this->concurrent->create($callable);
                                } else {
                                    Coroutine::create($callable);
                                }
                            } catch (Throwable) {
                                break;
                            } finally {
                                $callable = null;
                                $args = null;
                            }
                        }
                    }
                } catch (Throwable $e) {
                } finally {
                    $this->close();
                }
            });
        });

        // Wait for the worker exit event
        $this->waitingWorkerExit ??= Coroutine::create(function () {
            try {
                CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
                $this->close();
                $this->workerExited = true;
            } catch (Throwable) {
            }
        });
    }

    protected function close(): void
    {
        $chan = $this->chan;
        $chan?->close();

        if ($this->chan === $chan) {
            $this->chan = null;
        }
    }
}
