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
use Hyperf\Coroutine\Coroutine;
use Sentry\HttpClient\Request;
use Sentry\HttpClient\Response;
use Sentry\Options;
use Swoole\Coroutine\Channel;
use Throwable;

class AsyncHttpClient extends \Sentry\HttpClient\HttpClient
{
    protected ?Channel $chan = null;

    protected int $channelSize = 65535;

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
            while (true) {
                while (true) {
                    /** @var Closure|null $closure */
                    $closure = $this->chan?->pop();
                    if (! $closure) {
                        break 2;
                    }
                    try {
                        $closure();
                    } catch (Throwable) {
                        break;
                    } finally {
                        $closure = null;
                    }
                }
            }

            $this->close();
        });

        Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                $this->close();
            }
        });
    }
}
