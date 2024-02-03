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
use FriendsOfHyperf\Trigger\Traits\Logger;
use Hyperf\Collection\Arr;
use Hyperf\Di\Annotation\AnnotationCollector;
use SplPriorityQueue;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class SubscriberManager
{
    use Logger;

    protected array $subscribers = [];

    protected ?LoggerInterface $logger = null;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $this->getLogger();
    }

    public function register()
    {
        /** @var Subscriber[] $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(Subscriber::class);
        $queue = new SplPriorityQueue();

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
