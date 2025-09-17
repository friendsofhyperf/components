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

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\Serializer\PayloadSerializer;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;
use Throwable;

use function Hyperf\Coroutine\go;
use function Hyperf\Support\msleep;

class AsyncHttpTransport implements TransportInterface
{
    protected TransportInterface $transport;

    protected ?Channel $chan = null;

    protected bool $workerExited = false;

    protected ?Coroutine $workerWatcher = null;

    public function __construct(
        protected ClientBuilder $clientBuilder,
        protected int $channelSize = 65535,
    ) {
        $this->transport = new \Sentry\Transport\HttpTransport(
            $this->clientBuilder->getOptions(),
            $this->clientBuilder->getHttpClient(),
            new PayloadSerializer($this->clientBuilder->getOptions()),
            $this->clientBuilder->getLogger()
        );
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
        $this->chan?->close();
        $this->chan = null;

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

        go(function () {
            while (true) {
                /** @var Event|false|null $event */
                $event = $this->chan->pop();

                if (! $event) {
                    break;
                }

                try {
                    $this->transport->send($event);
                } catch (Throwable $e) {
                    break;
                } finally {
                    // Prevent memory leak
                    $event = null;
                }
            }

            $this->close();
        });

        $this->workerWatcher ??= Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                $this->workerExited = true;

                while (! $this->chan?->isEmpty()) {
                    msleep(100);
                }

                $this->close();
            }
        });
    }
}
