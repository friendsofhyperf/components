<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Lock\Listener;

use FriendsOfHyperf\Lock\Annotation\Lock;
use FriendsOfHyperf\Lock\LockFactory;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\ReflectionManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterPropertyHandlerListener implements ListenerInterface
{
    public function __construct(private LockFactory $lockFactory)
    {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        PropertyHandlerManager::register(Lock::class, function ($object, $currentClassName, $targetClassName, $property, $annotation) {
            if ($annotation instanceof Lock) {
                $reflectionProperty = ReflectionManager::reflectProperty($currentClassName, $property);
                $reflectionProperty->setAccessible(true);

                $name = $annotation->name;
                $seconds = (int) $annotation->seconds;
                $owner = $annotation->owner;
                $driver = $annotation->driver;

                $reflectionProperty->setValue($object, $this->lockFactory->make($name, $seconds, $owner, $driver));
            }
        });
    }
}
