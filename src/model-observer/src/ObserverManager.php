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

use FriendsOfHyperf\ModelObserver\Annotation\ObservedBy;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
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

        /** @var array<class-string,MultipleAnnotation> */
        $classes = AnnotationCollector::getClassesByAnnotation(Observer::class);

        foreach ($classes as $class => $multiAnnotation) {
            /** @var Observer[] $annotations */
            $annotations = $multiAnnotation->toAnnotations();

            foreach ($annotations as $annotation) {
                /** @var class-string<Model>[] $models */
                $models = (array) $annotation->model;
                $priority = $annotation->priority;

                foreach ($models as $model) {
                    if (! $model || ! class_exists($model)) {
                        continue;
                    }

                    $queues[$model] ??= new SplPriorityQueue();
                    $queues[$model]->insert($class, $priority);
                }
            }
        }

        /** @var array<class-string,MultipleAnnotation> */
        $classes = AnnotationCollector::getClassesByAnnotation(ObservedBy::class);

        foreach ($classes as $model => $multiAnnotation) {
            /** @var ObservedBy[] $annotations */
            $annotations = $multiAnnotation->toAnnotations();

            foreach ($annotations as $annotation) {
                /** @var class-string[] $classes */
                $classes = (array) $annotation->classes;
                $priority = $annotation->priority;

                foreach ($classes as $class) {
                    if (! $class || ! class_exists($class)) {
                        continue;
                    }

                    $queues[$model] ??= new SplPriorityQueue();
                    $queues[$model]->insert($class, $priority);
                }
            }
        }

        foreach ($queues as $model => $queue) {
            self::$container[$model] ??= [];

            /** @var class-string[] $queue */
            foreach ($queue as $observer) {
                if (! in_array($observer, self::$container[$model], true)) {
                    self::$container[$model][] = $observer;
                }
            }
        }
    }

    public static function get(string $model): array
    {
        return self::$container[$model] ?? [];
    }
}
