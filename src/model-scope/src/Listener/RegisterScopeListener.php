<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelScope\Listener;

use FriendsOfHyperf\ModelScope\Annotation\ScopedBy;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;
use SplPriorityQueue;

class RegisterScopeListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param Event $event
     */
    public function process(object $event): void
    {
        /** @var array<class-string<Model>,SplPriorityQueue> $queues */
        $queues = [];

        /** @var array<class-string<Model>,MultipleAnnotation> */
        $classes = AnnotationCollector::getClassesByAnnotation(ScopedBy::class);

        foreach ($classes as $model => $multiAnnotation) {
            /** @var ScopedBy[] $annotations */
            $annotations = $multiAnnotation->toAnnotations();

            foreach ($annotations as $annotation) {
                /** @var class-string<Scope>[] $classes */
                $classes = (array) $annotation->classes;
                $priority = $annotation->priority;

                foreach ($classes as $class) {
                    if (
                        ! $class
                        || ! class_exists($class)
                        || ! is_a($class, Scope::class, true)
                    ) {
                        continue;
                    }

                    $queues[$model] ??= new SplPriorityQueue();
                    $queues[$model]->insert($class, $priority);
                }
            }
        }

        foreach ($queues as $model => $queue) {
            /** @var class-string<Scope>[] $queue */
            foreach ($queue as $scope) {
                if (! $this->container->has($scope)) {
                    continue;
                }

                $model::addGlobalScope(
                    $this->container->get($scope)
                );
            }
        }
    }
}
