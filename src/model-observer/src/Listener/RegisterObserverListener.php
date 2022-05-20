<?php

declare(strict_types=1);
/**
 * This file is part of model-observer.
 *
 * @link     https://github.com/friendsofhyperf/model-observer
 * @document https://github.com/friendsofhyperf/model-observer/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelObserver\Listener;

use FriendsOfHyperf\ModelObserver\ObserverManager;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

/**
 * @Listener
 */
class RegisterObserverListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param Event $event
     */
    public function process(object $event)
    {
        ObserverManager::register();
    }
}
