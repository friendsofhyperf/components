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

abstract class Task implements TaskInterface
{
    public static ?Server $server;

    public static ?int $workerId;

    protected int $delay = 0;

    protected int $maxAttempts = 0;

    protected int $retryAfter = 0;

    abstract public function handle(): void;

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function setMaxAttempts(int $maxAttempts): void
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setRetryAfter(int $retryAfter): void
    {
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

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

            for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
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
