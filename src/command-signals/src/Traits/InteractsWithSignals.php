<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\CommandSignals\Traits;

use FriendsOfHyperf\CommandSignals\SignalRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TypeError;

use function Hyperf\Coroutine\defer;
use function Hyperf\Support\make;

trait InteractsWithSignals
{
    protected ?SignalRegistry $SignalRegistry = null;

    /**
     * Define a callback to be run when the given signal(s) occurs.
     *
     * @param callable(int $signal): void  $callback
     * @throws TypeError
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function trap(array|int $signo, callable $callback): void
    {
        if (! $this->SignalRegistry) {
            $this->SignalRegistry = make(SignalRegistry::class);
            defer(fn () => $this->SignalRegistry->unregister());
        }

        $this->SignalRegistry->register($signo, $callback);
    }
}
