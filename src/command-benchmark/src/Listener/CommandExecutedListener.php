<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandBenchmark\Listener;

use Hyperf\Collection\Collection;
use Hyperf\Command\Command;
use Hyperf\Command\Event\AfterHandle;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\ListenerProvider;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use WeakMap;

use function Hyperf\Collection\collect;

class CommandExecutedListener implements ListenerInterface
{
    private WeakMap $metrics;

    public function __construct(private ContainerInterface $container)
    {
        $this->metrics = new WeakMap();
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
            AfterHandle::class,
        ];
    }

    /**
     * @param BeforeHandle|AfterHandle $event
     */
    public function process(object $event): void
    {
        $command = $event->getCommand();
        if ($event instanceof BeforeHandle) {
            $this->metrics[$command] = [
                'start_at' => microtime(true),
                'start_memory' => memory_get_usage(true),
                'queries' => 0,
            ];

            /** @var ListenerProvider $listenerProvider */
            $listenerProvider = $this->container->get(ListenerProviderInterface::class);
            $listenerProvider->on(QueryExecuted::class, function () use ($command) {
                ++$this->metrics[$command]['queries'];
            });
        }
        if ($event instanceof AfterHandle) {
            $metrics = collect([
                'time' => $this->formatExecutionTime(microtime(true) - $this->metrics[$command]['start_at']),
                'memory' => round((memory_get_usage() - $this->metrics[$command]['start_memory']) / 1024 / 1024, 2) . 'MB',
                'queries' => $this->metrics[$command]['queries'],
            ]);
            $this->renderBenchmarkResults($command, $metrics);
            $this->metrics->offsetUnset($command);
        }
    }

    private function formatExecutionTime(float $executionTime): string
    {
        return match (true) {
            $executionTime >= 60 => sprintf('%dm %ds', floor($executionTime / 60), $executionTime % 60),
            $executionTime >= 1 => round($executionTime, 2) . 's',
            default => round($executionTime * 1000) . 'ms',
        };
    }

    private function renderBenchmarkResults(Command $command, Collection $metrics): void
    {
        $output = $metrics->map(fn ($value, $key) => match ($key) {
            'time' => "<bg=blue;fg=black> TIME: {$value} </>",
            'memory' => "<bg=green;fg=black> MEM: {$value} </>",
            'queries' => "<bg=yellow;fg=black> SQL: {$value} </>",
            'rows' => "<bg=magenta;fg=black> ROWS: {$value} </>",
        });

        $command->newLine();
        $command->line('⚡ ' . $output->join(' '));
        $command->newLine();
    }
}
