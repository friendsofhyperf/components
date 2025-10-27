<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CoPHPUnit\Concerns;

use Throwable;

trait RunTestsInCoroutine
{
    protected bool $enableCoroutine = true;

    public function runBare(): void
    {
        if ($this->enableCoroutine && extension_loaded('swoole') && \Swoole\Coroutine::getCid() === -1) {
            $exception = null;

            /* @phpstan-ignore-next-line */
            \Swoole\Coroutine\run(function () use (&$exception) {
                try {
                    parent::runBare();
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

            return;
        }

        parent::runBare();
    }
}
