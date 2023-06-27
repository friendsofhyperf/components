<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Tests\Concerns;

use Throwable;

/**
 * @method string name()
 */
trait RunTestsInCoroutine
{
    protected bool $enableCoroutine = true;

    protected string $realTestName = '';

    final protected function runTestsInCoroutine(...$arguments)
    {
        parent::setName($this->realTestName);

        $testResult = null;
        $exception = null;

        /* @phpstan-ignore-next-line */
        \Swoole\Coroutine\run(function () use (&$testResult, &$exception, $arguments) {
            try {
                $testResult = $this->{$this->realTestName}(...$arguments);
            } catch (Throwable $e) {
                $exception = $e;
            } finally {
                \Swoole\Timer::clearAll();
                \Hyperf\Coordinator\CoordinatorManager::until(\Hyperf\Coordinator\Constants::WORKER_EXIT)->resume();
            }
        });

        if ($exception) {
            throw $exception;
        }

        return $testResult;
    }

    final protected function runTest(): mixed
    {
        if (extension_loaded('swoole') && \Swoole\Coroutine::getCid() === -1 && $this->enableCoroutine) {
            $this->realTestName = $this->name();
            parent::setName('runTestsInCoroutine');
        }

        return parent::runTest();
    }
}
