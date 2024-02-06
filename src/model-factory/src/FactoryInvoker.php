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

use Faker\Factory as FakerFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Factory;
use Psr\Container\ContainerInterface;

use function Hyperf\Tappable\tap;

class FactoryInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);

        return tap(
            new Factory(
                FakerFactory::create('en_US')
            ),
            function ($factory) use ($config) {
                if (is_dir($path = $config->get('model_factory.path', ''))) {
                    $factory->load($path);
                }
            }
        );
    }
}
