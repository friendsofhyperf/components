<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Mail\Stubs;

use Closure;
use FriendsOfHyperf\Mail\Contract\Factory;
use FriendsOfHyperf\Mail\MailManager;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Mockery;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;

class ContainerStub
{
    public static function getContainer(?Closure $closure = null): ContainerInterface
    {
        $container = new Container(new DefinitionSource([]));

        $config = new Config([]);
        $container->set(ConfigInterface::class, $config);
        $mailerManager = new MailManager($container, $config);
        $container->set(Factory::class, $mailerManager);
        $events = Mockery::mock(EventDispatcherInterface::class);
        $container->set(EventDispatcherInterface::class, $events);

        $reflectionClass = new ReflectionClass(ApplicationContext::class);
        $reflectionClass->setStaticPropertyValue('container', $container);
        if ($closure === null) {
            return $container;
        }
        $closure($container);
        return $container;
    }

    public static function clear()
    {
        $reflectionClass = new ReflectionClass(ApplicationContext::class);
        $reflectionClass->setStaticPropertyValue('container', null);
    }
}
