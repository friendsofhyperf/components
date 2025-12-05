<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandSignals;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Signal;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\parallel;

class SignalRegistry
{
    /**
     * @var array<int, callable[]>
     */
    protected array $signalHandlers = [];

    /**
     * @var int[]
     */
    protected array $handling = [];

    public function __construct(protected int $timeout = 1, protected int $concurrentLimit = 0)
    {
    }

    /**
     * @param int|int[] $signo
     * @param (callable(int $signo): void) $signalHandler
     */
    public function register(int|array $signo, callable $signalHandler): void
    {
        if (is_array($signo)) {
            array_map(fn ($s) => $this->register((int) $s, $signalHandler), $signo);
            return;
        }

        $this->pushSignalHandler($signo, $signalHandler);
        $this->waitSignal($signo);
    }

    /**
     * @param null|int|int[] $signo
     */
    public function unregister(null|int|array $signo = null): void
    {
        match (true) {
            // Unregister all signals
            is_null($signo) => $this->signalHandlers = [],
            // Unregister multiple signals
            is_array($signo) => array_map(fn ($s) => isset($this->signalHandlers[$s]) && $this->signalHandlers[$s] = [], $signo),
            // Unregister single signal
            default => $this->signalHandlers[$signo] = [],
        };
    }

    /**
     * @param (callable(int $signo): void) $signalHandler
     */
    protected function pushSignalHandler(int $signo, callable $signalHandler): void
    {
        $this->signalHandlers[$signo] ??= [];
        $this->signalHandlers[$signo][] = $signalHandler;
    }

    protected function waitSignal(int $signo): void
    {
        if (isset($this->handling[$signo])) {
            return;
        }

        $this->handling[$signo] = Coroutine::create(function () use ($signo) {
            defer(fn () => posix_kill(posix_getpid(), $signo));

            while (true) {
                if (Signal::wait($signo, $this->timeout)) {
                    $callbacks = array_map(fn ($callback) => fn () => $callback($signo), $this->signalHandlers[$signo] ?? []);

                    return parallel($callbacks, $this->concurrentLimit);
                }
            }
        });
    }
}
