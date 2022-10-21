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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Utils\ApplicationContext;

abstract class Task implements TaskInterface
{
    protected int $delay;

    protected int $maxAttempts;

    protected int $retryAfter;

    abstract public function handle(): void;

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function setMaxAttempts(int $maxAttempts): void
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function setRetryAfter(int $retryAfter): void
    {
        $this->retryAfter = $retryAfter;
    }

    public static function deliver(TaskInterface|Closure $task, ?int $delay = null, ?int $maxAttempts = null, ?int $retryAfter = null): void
    {
        if ($task instanceof Closure) {
            $task = new ClosureTask($task);
        }

        $delay && $task->setDelay($delay);
        $maxAttempts && $task->setMaxAttempts($maxAttempts);
        $retryAfter && $task->setRetryAfter($retryAfter);

        $message = new TaskMessage($task);

        $processes = ProcessCollector::all();

        if ($processes) {
            $string = serialize($message);
            /** @var \Swoole\Process $process */
            foreach ($processes as $process) {
                $result = $process->exportSocket()->send($string, 10);
                if ($result === false) {
                    ApplicationContext::getContainer()->get(StdoutLoggerInterface::class)->error('Configuration synchronization failed. Please restart the server.');
                }
            }
        }
    }
}
