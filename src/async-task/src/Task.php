<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AsyncTask;

use Closure;
use FriendsOfHyperf\AsyncTask\Event\AfterHandle;
use FriendsOfHyperf\AsyncTask\Event\BeforeHandle;
use FriendsOfHyperf\AsyncTask\Event\FailedHandle;
use FriendsOfHyperf\AsyncTask\Event\RetryHandle;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Http\Server as CoHttpServer;
use Swoole\Coroutine\Server as CoServer;
use Swoole\Server;
use Throwable;

class Task
{
    /**
     * @var null|CoHttpServer|CoServer|Server
     */
    public static $server;

    public static ?int $workerId;

    public static function deliver(TaskInterface|Closure $task, ?int $delay = null, ?int $maxAttempts = null, ?int $retryAfter = null): void
    {
        if ($task instanceof Closure) {
            $task = new ClosureTask($task);
        }

        $delay && $task->setDelay($delay);
        $maxAttempts && $task->setMaxAttempts($maxAttempts);
        $retryAfter && $task->setRetryAfter($retryAfter);
        $message = new TaskMessage($task);

        if (self::$server instanceof Server) {
            $workerCount = self::$server->setting['worker_num'] + (self::$server->setting['task_worker_num'] ?? 0) - 1;
            $workers = range(0, $workerCount);
            shuffle($workers);

            foreach ($workers as $workerId) {
                if ($workerId == self::$workerId) {
                    continue;
                }

                self::$server->sendMessage($message, $workerId);

                break;
            }
        } else {
            go(static fn () => self::execute($message));
        }
    }

    public static function deliverIf($condition, TaskInterface|Closure $task, ?int $delay = null, ?int $maxAttempts = null, ?int $retryAfter = null): void
    {
        if ($condition) {
            static::deliver($task, $delay, $maxAttempts, $retryAfter);
        }
    }

    public static function deliverUnless($condition, TaskInterface|Closure $task, ?int $delay = null, ?int $maxAttempts = null, ?int $retryAfter = null): void
    {
        static::deliverIf(! $condition, $task, $delay, $maxAttempts, $retryAfter);
    }

    public static function execute(TaskMessage $message): void
    {
        $container = ApplicationContext::getContainer();
        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        if ($message->getDelay()) {
            Coroutine::sleep((float) $message->getDelay());
        }

        try {
            $eventDispatcher->dispatch(new BeforeHandle($message));

            retry($message->getMaxAttempts(), function ($attempts, $e) use ($message, $eventDispatcher) {
                $attempts > 1 && $eventDispatcher->dispatch(new RetryHandle($message, $e));
                $message->task()->handle();
            }, $message->getRetryAfter() * 1000);

            $eventDispatcher->dispatch(new AfterHandle($message));
        } catch (Throwable $e) {
            $eventDispatcher->dispatch(new FailedHandle($message, $e));
        }
    }
}
