<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelObserver;

use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use Hyperf\Di\Annotation\AnnotationCollector;
use SplPriorityQueue;

class ObserverManager
{
    private static array $container = [];

    public static function register(): void
    {
        /** @var SplPriorityQueue[] $queues */
        $queues = [];
        /** @var array<string,Observer> */
        $classes = AnnotationCollector::getClassesByAnnotation(Observer::class);

        foreach ($classes as $class => $property) {
            $model = $property->model;

            if (! $model || ! class_exists($model)) {
                continue;
            }

            if (! isset($queues[$model])) {
                $queues[$model] = new SplPriorityQueue();
            }

            $queues[$model]->insert($class, $property->priority);
        }

        foreach ($queues as $class => $queue) {
            if (! isset(self::$container[$class])) {
                self::$container[$class] = [];
            }

            foreach ($queue as $observer) {
                self::$container[$class][] = $observer;
            }
        }
    }

    public static function get(string $model): array
    {
        return self::$container[$model] ?? [];
    }
}
