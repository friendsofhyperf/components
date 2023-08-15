<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelObserver\Listener;

use FriendsOfHyperf\ModelObserver\ObserverManager;
use Hyperf\Database\Model\Events\Booted;
use Hyperf\Database\Model\Events\Booting;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\ForceDeleted;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Restoring;
use Hyperf\Database\Model\Events\Retrieved;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Saving;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\Database\Model\Events\Updating;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

class ObserverListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            Booted::class,
            Booting::class,
            Created::class,
            Creating::class,
            Deleted::class,
            Deleting::class,
            ForceDeleted::class,
            Restored::class,
            Restoring::class,
            Retrieved::class,
            Saved::class,
            Saving::class,
            Updated::class,
            Updating::class,
        ];
    }

    /**
     * @param Event $event
     */
    public function process(object $event): void
    {
        if (! $event instanceof Event) {
            return;
        }

        $model = $event->getModel();
        $modelClass = $model::class;
        $method = $event->getMethod();
        $observers = ObserverManager::get($modelClass);

        foreach ($observers as $observerClass) {
            if (! $this->container->has($observerClass)) {
                continue;
            }

            $observer = $this->container->get($observerClass);

            if (! is_callable([$observer, $method])) {
                continue;
            }

            $observer->{$method}($model);
        }
    }
}
