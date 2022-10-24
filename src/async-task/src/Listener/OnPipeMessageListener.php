<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AsyncTask\Listener;

use FriendsOfHyperf\AsyncTask\Event\AfterHandle;
use FriendsOfHyperf\AsyncTask\Event\BeforeHandle;
use FriendsOfHyperf\AsyncTask\Event\FailedHandle;
use FriendsOfHyperf\AsyncTask\Event\RetryHandle;
use FriendsOfHyperf\AsyncTask\TaskMessage;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Utils\Backoff;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    /**
     * @param OnPipeMessage $event
     */
    public function process(object $event): void
    {
        $message = $event->data;

        if (! $message instanceof TaskMessage) {
            return;
        }

        Coroutine::create(function () use ($message) {
            $eventDispatcher = $this->container->get(EventDispatcherInterface::class);

            if ($message->getDelay()) {
                Coroutine::sleep((float) $message->getDelay());
            }

            try {
                $eventDispatcher->dispatch(new BeforeHandle($message));

                $this->retry($message->getMaxAttempts(), function ($attempts, $e) use ($message, $eventDispatcher) {
                    $attempts > 1 && $eventDispatcher->dispatch(new RetryHandle($message, $e));
                    $message->task()->handle();
                }, $message->getRetryAfter() * 1000);

                $eventDispatcher->dispatch(new AfterHandle($message));
            } catch (Throwable $e) {
                $eventDispatcher->dispatch(new FailedHandle($message, $e));
            }
        });
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
