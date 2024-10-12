<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob\Listener;

use FriendsOfHyperf\AmqpJob\JobConsumerManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;

/**
 * Must handle the event before `Hyperf\Process\Listener\BootProcessListener`.
 */
class RegisterJobConsumer implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        if (! $this->isEnable()) {
            return;
        }

        $consumerManager = $this->container->get(JobConsumerManager::class);
        $consumerManager->run();
    }

    protected function isEnable(): bool
    {
        if (! $this->container->has(ConfigInterface::class)) {
            return true;
        }

        $config = $this->container->get(ConfigInterface::class);

        return (bool) $config->get('amqp.enable', true);
    }
}
