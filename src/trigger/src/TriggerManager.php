<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

use FriendsOfHyperf\Trigger\Annotation\Trigger;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Stdlib\SplPriorityQueue;

use function Hyperf\Support\class_basename;

class TriggerManager
{
    protected array $triggers = [];

    public function __construct(protected ConfigInterface $config)
    {
    }

    public function register(): void
    {
        /** @var Trigger[] $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(Trigger::class);
        $queue = new SplPriorityQueue();

        foreach ($classes as $class => $property) {
            if ($property->events == ['*']) {
                $property->events = ['write', 'update', 'delete'];
            }
            $queue->insert([$class, $property], $property->priority);
        }

        foreach ($queue as $value) {
            /** @var Trigger $property */
            [$class, $property] = $value;

            foreach ($property->events as $eventType) {
                $config = $this->config->get('trigger.connections.' . $property->connection);
                $property->table ??= class_basename($class);
                $property->database ??= $config['databases_only'][0] ?? '';

                $key = $this->buildKey($property->connection, $property->database, $property->table, $eventType);
                $method = 'on' . ucfirst($eventType);

                $items = Arr::get($this->triggers, $key, []);
                $items[] = [$class, $method];

                Arr::set($this->triggers, $key, $items);
            }
        }
    }

    public function get(string $key): array
    {
        return Arr::get($this->triggers, $key, []);
    }

    public function getDatabases(string $connection): array
    {
        return array_keys($this->get($connection));
    }

    public function getTables(string $connection): array
    {
        $tables = [];

        foreach ($this->getDatabases($connection) as $database) {
            $tables = [...$tables, ...array_keys($this->get($this->buildKey($connection, $database)))];
        }

        return $tables;
    }

    private function buildKey(...$arguments): string
    {
        return join('.', $arguments);
    }
}
