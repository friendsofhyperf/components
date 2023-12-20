<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelFactory;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Factory;
use Hyperf\Database\Model\FactoryBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TypeError;

/**
 * @return FactoryBuilder|null
 * @throws TypeError
 * @throws NotFoundExceptionInterface
 * @throws ContainerExceptionInterface
 */
function factory(string $class)
{
    if (! ApplicationContext::hasContainer()) {
        return null;
    }

    $container = ApplicationContext::getContainer();

    $factory = $container->get(Factory::class);
    $arguments = func_get_args();

    if (isset($arguments[1]) && is_string($arguments[1])) {
        return $factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? null);
    }

    if (isset($arguments[1])) {
        return $factory->of($arguments[0])->times($arguments[1]);
    }

    return $factory->of($arguments[0]);
}
