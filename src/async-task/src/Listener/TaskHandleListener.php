<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask\Listener;

use FriendsOfHyperf\AsyncTask\ClosureTask;
use FriendsOfHyperf\AsyncTask\Event\AfterHandle;
use FriendsOfHyperf\AsyncTask\Event\BeforeHandle;
use FriendsOfHyperf\AsyncTask\Event\Event;
use FriendsOfHyperf\AsyncTask\Event\FailedHandle;
use FriendsOfHyperf\AsyncTask\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TaskHandleListener implements ListenerInterface
{
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('task');
    }

    public function listen(): array
    {
        return [
            AfterHandle::class,
            BeforeHandle::class,
            FailedHandle::class,
            RetryHandle::class,
        ];
    }

    /**
     * @param FailedHandle $event
     */
    public function process(object $event): void
    {
        if ($event instanceof Event) {
            $task = $event->getMessage()->task();
            $taskClass = get_class($task);

            if ($task instanceof ClosureTask) {
                $taskClass = sprintf('Task[%s@%s]', $task->class, $task->method);
            }

            $date = date('Y-m-d H:i:s');

            switch (true) {
                case $event instanceof BeforeHandle:
                    $this->logger->info(sprintf('[%s] Processing %s.', $date, $taskClass));
                    break;
                case $event instanceof AfterHandle:
                    $this->logger->info(sprintf('[%s] Processed %s.', $date, $taskClass));
                    break;
                case $event instanceof FailedHandle:
                    $this->logger->error(sprintf('[%s] Failed %s.', $date, $taskClass));
                    $this->logger->error((string) $event->getThrowable());
                    break;
                case $event instanceof RetryHandle:
                    $this->logger->warning(sprintf('[%s] Retried %s.', $date, $taskClass));
                    break;
            }
        }
    }
}
