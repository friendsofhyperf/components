<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\Logger;
use Hyperf\Logger\LoggerFactory;

/**
 * @method static void emergency(string|\Stringable $message, array $context = [])
 * @method static void alert(string|\Stringable $message, array $context = [])
 * @method static void critical(string|\Stringable $message, array $context = [])
 * @method static void error(string|\Stringable $message, array $context = [])
 * @method static void warning(string|\Stringable $message, array $context = [])
 * @method static void notice(string|\Stringable $message, array $context = [])
 * @method static void info(string|\Stringable $message, array $context = [])
 * @method static void debug(string|\Stringable $message, array $context = [])
 * @method static void log($level, string|\Stringable $message, array $context = [])
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
