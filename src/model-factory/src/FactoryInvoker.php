<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelFactory;

use Faker\Factory as FakerFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Factory;
use Psr\Container\ContainerInterface;

class FactoryInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $factory = new Factory(
            FakerFactory::create('en_US')
        );

        if (is_dir($path = $config->get('model_factory.path', ''))) {
            $factory->load($path);
        }

        return $factory;
    }
}
