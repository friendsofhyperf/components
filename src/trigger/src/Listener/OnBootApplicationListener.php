<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Trigger\Listener;

use FriendsOfHyperf\Trigger\ConsumerManager;
use FriendsOfHyperf\Trigger\SubscriberManager;
use FriendsOfHyperf\Trigger\TriggerManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class OnBootApplicationListener implements ListenerInterface
{
    public function __construct(
        protected SubscriberManager $subscriberManager,
        protected TriggerManager $triggerManager,
        protected ConsumerManager $consumerManager
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param BootApplication $event
     */
    public function process(object $event): void
    {
        $this->subscriberManager->register();
        $this->triggerManager->register();
        $this->consumerManager->run();
    }
}
