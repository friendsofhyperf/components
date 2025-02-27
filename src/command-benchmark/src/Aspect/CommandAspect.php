<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\CommandBenchmark\Aspect;

use Hyperf\Collection\Collection;
use Hyperf\Command\Command;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Event\ListenerProvider;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use WeakMap;

use function Hyperf\Collection\collect;

#[Aspect()]
class CommandAspect extends AbstractAspect
{
    public array $classes = [
        Command::class . '::__construct',
        Command::class . '::execute',
    ];

    private WeakMap $metrics;

    public function __construct(private ContainerInterface $container)
    {
        $this->metrics = new WeakMap();
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var Command $command */
        $command = $proceedingJoinPoint->getInstance();
        $method = $proceedingJoinPoint->methodName;
        $result = $proceedingJoinPoint->process();

        if ($method === '__construct') {
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

            $command->addOption('enable-benchmark', null, null, 'Enable benchmark current command');
            // $command->addOption('tableToWatch', null, null, 'Table to watch');
        }

        if ($method === 'execute' && $command->option('enable-benchmark')) {
            $metrics = collect([
                'time' => $this->formatExecutionTime(microtime(true) - $this->metrics[$command]['start_at']),
                'memory' => round((memory_get_usage() - $this->metrics[$command]['start_memory']) / 1024 / 1024, 2) . 'MB',
                'queries' => $this->metrics[$command]['queries'],
            ]);
            $this->renderBenchmarkResults($command, $metrics);
            $this->metrics->offsetUnset($command);
        }

        return $result;
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
            // 'rows' => "<bg=magenta;fg=black> ROWS: {$value} </>",
            default => '',
        });

        $command->newLine();
        $command->line('⚡ ' . $output->join(' '));
        $command->newLine();
    }
}
