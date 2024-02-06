<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelObserver;

use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Stdlib\SplPriorityQueue;

class ObserverManager
{
    /**
     * @var array<class-string<Model>,class-string[]>
     */
    private static array $container = [];

    public static function register(): void
    {
        /** @var array<class-string<Model>,SplPriorityQueue> $queues */
        $queues = [];
        /** @var array<class-string,Observer> */
        $classes = AnnotationCollector::getClassesByAnnotation(Observer::class);

        foreach ($classes as $class => $property) {
            /** @var class-string<Model>[] */
            $models = (array) $property->model;
            $priority = $property->priority;

            foreach ($models as $model) {
                if (! $model || ! class_exists($model)) {
                    continue;
                }

                $queues[$model] ??= new SplPriorityQueue();
                $queues[$model]->insert($class, $priority);
            }
        }

        foreach ($queues as $model => $queue) {
            if (! isset(self::$container[$model])) {
                self::$container[$model] = [];
            }

            foreach ($queue as $observer) {
                self::$container[$model][] = $observer;
            }
        }
    }

    public static function get(string $model): array
    {
        return self::$container[$model] ?? [];
    }
}
