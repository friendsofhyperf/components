<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade;

use Hyperf\Logger\Logger;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * @mixin LoggerFactory
 * @mixin Logger
 */
class Log extends Facade
{
    public static function __callStatic($name, $arguments)
    {
        return self::channel()->{$name}(...$arguments);
    }

    /**
     * @param string $name
     * @param string $group
     * @return \Psr\Log\LoggerInterface
     * @throws TypeError
     */
    public static function channel($name = 'hyperf', $group = 'default')
    {
        return ApplicationContext::getContainer()
            ->get(static::getFacadeAccessor())
            ->get($name, $group);
    }

    protected static function getFacadeAccessor()
    {
        return LoggerFactory::class;
    }
}
