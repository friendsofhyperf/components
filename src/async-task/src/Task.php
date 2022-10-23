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
use FriendsOfHyperf\AsyncTask\Process\AsyncTaskConsumer;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Utils\ApplicationContext;

abstract class Task implements TaskInterface
{
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

        $container = ApplicationContext::getContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        $consumerName = $container->get(AsyncTaskConsumer::class)->name ?? '';

        /** @var \Swoole\Process[] $processes */
        $processes = ProcessCollector::get($consumerName);

        if (! $processes) {
            $logger->warning(sprintf('Async task consumer [%s] is not running.', $consumerName));
            return;
        }

        $message = new TaskMessage($task);
        $string = serialize($message);
        $rand = array_rand($processes);
        $result = $processes[$rand]->exportSocket()->send($string, 10);

        if ($result === false) {
            $logger->error('Configuration synchronization failed. Please restart the server.');
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
