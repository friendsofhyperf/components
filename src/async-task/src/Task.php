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
use Swoole\Server;

class Task
{
    public static ?Server $server;

    public static ?int $workerId;

    public static function deliver(TaskInterface|Closure $task, ?int $delay = null, ?int $maxAttempts = null, ?int $retryAfter = null): void
    {
        if ($task instanceof Closure) {
            $task = new ClosureTask($task);
        }

        $delay && $task->setDelay($delay);
        $maxAttempts && $task->setMaxAttempts($maxAttempts);
        $retryAfter && $task->setRetryAfter($retryAfter);

        if (self::$server instanceof Server) {
            $message = new TaskMessage($task);
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
}
