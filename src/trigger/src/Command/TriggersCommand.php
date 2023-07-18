<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Trigger\Command;

use FriendsOfHyperf\Trigger\Annotation\Trigger;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\class_basename;

class TriggersCommand extends HyperfCommand
{
    protected ?string $signature = 'describe:triggers {--C|connection= : connection} {--T|table= : Table}';

    protected string $description = 'List all triggers.';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
    }

    public function handle()
    {
        $triggers = AnnotationCollector::getClassesByAnnotation(Trigger::class);

        $rows = collect($triggers)
            ->each(function ($property, $class) {
                /* @var Trigger $property */
                $property->table ??= class_basename($class);
            })
            ->filter(function ($property, $class) {
                /* @var Trigger $property */
                if ($this->input->getOption('connection')) {
                    return $this->input->getOption('connection') == $property->connection;
                }
                return true;
            })
            ->filter(function ($property, $class) {
                /* @var Trigger $property */
                if ($this->input->getOption('table')) {
                    return $this->input->getOption('table') == $property->table;
                }
                return true;
            })
            ->transform(fn ($property, $class) => [$property->connection, $property->database, $property->table, implode(',', $property->events), $class, $property->priority]);

        $this->info('Triggers:');
        $this->table(['connection', 'Database', 'Table', 'Events', 'Trigger', 'Priority'], $rows);
    }
}
