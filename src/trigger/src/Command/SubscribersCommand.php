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

use FriendsOfHyperf\Trigger\Annotation\Subscriber;
use FriendsOfHyperf\Trigger\Subscriber\SnapshotSubscriber;
use FriendsOfHyperf\Trigger\Subscriber\TriggerSubscriber;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;

class SubscribersCommand extends HyperfCommand
{
    protected ?string $signature = 'describe:subscribers {--C|connection= : connection}';

    protected string $description = 'List all subscribers.';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
    }

    public function handle()
    {
        $subscribers = AnnotationCollector::getClassesByAnnotation(Subscriber::class);
        $rows = collect($subscribers)
            ->filter(function ($property, $class) {
                if ($connection = $this->input->getOption('connection')) {
                    return $connection == $property->connection;
                }
                return true;
            })
            ->transform(fn ($property, $class) => [$property->connection, $class, $property->priority])
            ->merge([
                ['[default]', SnapshotSubscriber::class, 1],
                ['[default]', TriggerSubscriber::class, 1],
            ]);

        $this->info('Subscribers:');
        $this->table(['Connection', 'Subscriber', 'Priority'], $rows);
    }
}
