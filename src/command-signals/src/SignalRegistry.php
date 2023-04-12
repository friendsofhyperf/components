<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\CommandSignals;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Signal;

use function Hyperf\Coroutine\defer;

class SignalRegistry
{
    protected array $signalHandlers = [];

    /**
     * @var int[]
     */
    protected array $handling = [];

    protected bool $unregistered = false;

    public function __construct(protected int $timeout = 1, protected int $concurrent = 0)
    {
    }

    public function register(int|array $signo, callable $signalHandler): void
    {
        if (is_array($signo)) {
            array_map(fn ($s) => $this->register((int) $s, $signalHandler), $signo);
            return;
        }

        $this->signalHandlers[$signo] ??= [];
        $this->signalHandlers[$signo][] = $signalHandler;

        if ($this->isSignalHandling($signo)) {
            return;
        }

        $this->handleSignal($signo);
    }

    public function unregister(): void
    {
        $this->unregistered = true;
    }

    protected function handleSignal(int $signo): void
    {
        $this->handling[$signo] = Coroutine::create(function () use ($signo) {
            defer(fn () => posix_kill(posix_getpid(), $signo));

            while (true) {
                if (Signal::wait($signo, $this->timeout)) {
                    $callbacks = array_map(fn ($callback) => fn () => $callback($signo), $this->signalHandlers[$signo]);

                    return parallel($callbacks, $this->concurrent);
                }

                if ($this->unregistered) {
                    break;
                }
            }
        });
    }

    protected function isSignalHandling(int $signo): bool
    {
        return isset($this->handling[$signo]);
    }
}
