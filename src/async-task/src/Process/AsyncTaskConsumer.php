<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AsyncTask\Process;

use FriendsOfHyperf\AsyncTask\Event\AfterHandle;
use FriendsOfHyperf\AsyncTask\Event\BeforeHandle;
use FriendsOfHyperf\AsyncTask\Event\FailedHandle;
use FriendsOfHyperf\AsyncTask\Event\RetryHandle;
use FriendsOfHyperf\AsyncTask\TaskMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Exception\SocketAcceptException;
use Hyperf\Utils\Backoff;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Throwable;

class AsyncTaskConsumer extends AbstractProcess
{
    /**
     * Cannot change the name of the process.
     */
    public string $name = 'async-task-consumer';

    public function __construct(ContainerInterface $container, protected StdoutLoggerInterface $logger)
    {
        parent::__construct($container);
    }

    public function handle(): void
    {
        $quit = new Channel(1);

        Coroutine::create(function () use ($quit) {
            while ($quit->pop(0.001) !== true) {
                try {
                    /** @var \Swoole\Coroutine\Socket $sock */
                    $sock = $this->process->exportSocket();
                    $recv = $sock->recv($this->recvLength, $this->recvTimeout);

                    if ($recv === '') {
                        throw new SocketAcceptException('Socket is closed', $sock->errCode);
                    }

                    if ($recv === false && $sock->errCode !== SOCKET_ETIMEDOUT) {
                        throw new SocketAcceptException('Socket is closed', $sock->errCode);
                    }

                    if ($recv !== false && $message = unserialize($recv)) {
                        if ($message instanceof TaskMessage) {
                            Coroutine::create(function () use ($message) {
                                if ($message->getDelay()) {
                                    Coroutine::sleep((float) $message->getDelay());
                                }

                                try {
                                    $this->event && $this->event->dispatch(new BeforeHandle($message));

                                    $this->retry($message->getMaxAttempts(), function ($attempts, $e) use ($message) {
                                        $attempts > 1 && $this->event && $this->event->dispatch(new RetryHandle($message, $e));
                                        $message->task()->handle();
                                    }, $message->getRetryAfter() * 1000);

                                    $this->event && $this->event->dispatch(new AfterHandle($message));
                                } catch (Throwable $e) {
                                    $this->event && $this->event->dispatch(new FailedHandle($message, $e));
                                }
                            });
                        }
                    }
                } catch (Throwable $exception) {
                    $this->logThrowable($exception);
                    if ($exception instanceof SocketAcceptException) {
                        // TODO: Reconnect the socket.
                        break;
                    }
                }
            }
            $quit->close();
        });

        CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
    }

    /**
     * override.
     */
    protected function listen(Channel $quit)
    {
    }

    protected function retry($times, callable $callback, int $sleep = 0)
    {
        $attempts = 0;
        $backoff = new Backoff($sleep);

        beginning:
        try {
            return $callback(++$attempts, $e ?? null);
        } catch (Throwable $e) {
            if (--$times < 0) {
                throw $e;
            }

            $backoff->sleep();
            goto beginning;
        }
    }
}
