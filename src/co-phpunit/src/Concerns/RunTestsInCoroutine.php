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

use FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine;
use ReflectionClass;
use Throwable;

trait RunTestsInCoroutine
{
    /**
     * @deprecated since v3.1, will be removed in v3.2, use `#[NonCoroutine]` instead.
     */
    protected bool $enableCoroutine = true;

    public function runBare(): void
    {
        if ($this->isCoroutineEnabled()) {
            $exception = null;

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

    private function isCoroutineEnabled(): bool
    {
        if (! $this->enableCoroutine) {
            return false;
        }

        if (! extension_loaded('swoole') || \Swoole\Coroutine::getCid() !== -1) {
            return false;
        }

        $refClass = new ReflectionClass(static::class);
        foreach ($refClass->getAttributes(NonCoroutine::class) as $attribute) {
            return false;
        }

        $refMethod = $refClass->getMethod($this->name());
        foreach ($refMethod->getAttributes(NonCoroutine::class) as $attribute) {
            return false;
        }

        return true;
    }
}
