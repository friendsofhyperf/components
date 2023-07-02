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

use Hyperf\Database\Model\Factory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                Factory::class => FactoryInvoker::class,
            ],
            'publish' => [
                [
                    'id' => 'model_factory',
                    'description' => 'The config for model factory.',
                    'source' => __DIR__ . '/../publish/model_factory.php',
                    'destination' => BASE_PATH . '/config/autoload/model_factory.php',
                ],
                [
                    'id' => 'model_factory_example',
                    'description' => 'The example for model factory.',
                    'source' => __DIR__ . '/../publish/example.php.stub',
                    'destination' => BASE_PATH . '/database/factory/ModelFactory.php',
                ],
            ],
        ];
    }
}
