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
use Hyperf\Di\Exception\NotFoundException;
use RuntimeException;

abstract class Facade
{
    /**
     * The resolved object instances.
     */
    protected static array $resolvedInstance = [];

    /**
     * Handle dynamic, static calls to the object.
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->{$name}(...$arguments);
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @return mixed
     */
    protected static function resolveFacadeInstance(object|string $name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        if (! ApplicationContext::getContainer()->has($name)) {
            throw new NotFoundException(sprintf('Entry %s not found.', $name));
        }

        return static::$resolvedInstance[$name] = ApplicationContext::getContainer()->get($name);
    }

    /**
     * Get the registered name of the component.
     *
     * @return object|string
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }
}
