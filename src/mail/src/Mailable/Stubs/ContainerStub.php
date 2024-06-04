<?php

namespace FriendsOfHyperf\Mail\Mailable\Stubs;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;

class ContainerStub
{
    public static function getContainer(?\Closure $closure = null): ContainerInterface
    {
        $container = new Container(new DefinitionSource([]));
        $reflectionClass = new \ReflectionClass(ApplicationContext::class);
        $reflectionClass->setStaticPropertyValue('container', $container);
        if ($closure === null) {
            return $container;
        }
        $closure($container);
        return $container;
    }

    public static function clear()
    {
        $reflectionClass = new \ReflectionClass(ApplicationContext::class);
        $reflectionClass->setStaticPropertyValue('container', null);
    }
}