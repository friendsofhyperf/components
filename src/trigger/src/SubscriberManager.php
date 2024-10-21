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

use FriendsOfHyperf\Trigger\Annotation\Subscriber;
use FriendsOfHyperf\Trigger\Subscriber\SnapshotSubscriber;
use FriendsOfHyperf\Trigger\Subscriber\TriggerSubscriber;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Stdlib\SplPriorityQueue;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriberManager
{
    protected array $subscribers = [];

    protected array $defaultSubscribers = [TriggerSubscriber::class, SnapshotSubscriber::class];

    public function __construct(
        protected ContainerInterface $container,
        protected ?LoggerInterface $logger = null
    ) {
    }

    public function register()
    {
        $queue = new SplPriorityQueue();
        // Register subscribers from config.
        $config = $this->container->get(ConfigInterface::class);

        foreach ($config->get('trigger.connections', []) as $connection => $item) {
            $subscribers = $item['subscribers'] ?? $this->defaultSubscribers;
            foreach ($subscribers as $priority => $class) {
                [$class, $priority] = is_numeric($class) ? [$priority, $class] : [$class, 0];

                if (! class_exists($class)) {
                    $this->logger?->warning(sprintf('[trigger.%s] %s not exists.', $connection, $class));
                    continue;
                }

                if (! is_subclass_of($class, EventSubscriberInterface::class)) {
                    $this->logger?->warning(sprintf('[trigger.%s] %s not implement %s.', $connection, $class, EventSubscriberInterface::class));
                    continue;
                }

                $property = new Subscriber($connection, $priority);
                $queue->insert([$class, $property], $priority);
            }
        }

        // Register subscribers from annotations.

        /** @var Subscriber[] $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(Subscriber::class);

        foreach ($classes as $class => $property) {
            $queue->insert([$class, $property], $property->priority);
        }

        foreach ($queue as $value) {
            [$class, $property] = $value;
            $this->subscribers[$property->connection] ??= [];
            $this->subscribers[$property->connection][] = $class;

            $this->logger?->debug(sprintf(
                '[trigger.%s] %s registered by %s process by %s.',
                $property->connection,
                $this::class,
                $class,
                $this::class
            ));
        }
    }

    public function get(string $connection = 'default'): array
    {
        return (array) Arr::get($this->subscribers, $connection, []);
    }
}
