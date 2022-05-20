<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade;

use Hyperf\Utils\ApplicationContext;
use RuntimeException;

abstract class Facade
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @throws \RuntimeException
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (! ApplicationContext::getContainer()->has(static::getFacadeAccessor())) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return ApplicationContext::getContainer()->get(static::getFacadeAccessor())->{$method}(...$args);
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }
}
