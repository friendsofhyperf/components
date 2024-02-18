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

use Closure;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Sentry\HttpClient\Request;
use Sentry\HttpClient\Response;
use Sentry\Options;
use Throwable;

class HttpClient extends \Sentry\HttpClient\HttpClient
{
    protected ?Channel $chan = null;

    protected ?Concurrent $concurrent = null;

    protected bool $waitingWorkerExit = false;

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
        $this->loop();

        $chan = $this->chan;
        $chan->push(fn () => parent::sendRequest($request, $options));

        return new Response(202, [], 'Waiting for sendRequest');
    }

    public function close(): void
    {
        $chan = $this->chan;
        $this->chan = null;

        $chan?->close();
    }

    protected function loop(): void
    {
        if ($this->chan != null) {
            return;
        }

        $this->chan = new Channel($this->channelSize);

        Coroutine::create(function () {
            try {
                while (true) {
                    while (true) {
                        /** @var Closure|null $closure */
                        $closure = $this->chan?->pop();
                        if (! $closure) {
                            break 2;
                        }
                        try {
                            if ($this->concurrent) {
                                $this->concurrent->create($closure);
                            } else {
                                Coroutine::create($closure);
                            }
                        } catch (Throwable) {
                            break;
                        } finally {
                            $closure = null;
                        }
                    }
                }
            } catch (Throwable $e) {
            } finally {
                $this->close();
            }
        });

        if (! $this->waitingWorkerExit) {
            Coroutine::create(function () {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                    $this->close();
                }
            });
            $this->waitingWorkerExit = true;
        }
    }
}
