<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker;

use Hyperf\Contract\ConfigInterface;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'commands' => [
                Console\InstallCommand::class,
            ],
            'dependencies' => [
                OutputModifiers\OutputModifier::class => function ($container) {
                    $config = $container->get(ConfigInterface::class);
                    $outputModifier = $config->get('web-tinker.output_modifier', OutputModifiers\PrefixDateTime::class);
                    return $container->get($outputModifier);
                },
            ],
            'listeners' => [
                Listener\RegisterRoutesListener::class => -1,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for web-tinker.',
                    'source' => __DIR__ . '/../publish/web-tinker.php',
                    'destination' => BASE_PATH . '/config/autoload/web-tinker.php',
                ],
                // [
                //     'id' => 'view',
                //     'description' => 'The view for web-tinker.',
                //     'source' => __DIR__ . '/../resources/views/web-tinker.blade.php',
                //     'destination' => BASE_PATH . '/resources/views/vendor/web-tinker/web-tinker.blade.php',
                // ],
                // [
                //     'id' => 'assets',
                //     'description' => 'The assets for web-tinker.',
                //     'source' => __DIR__ . '/../publish/assets',
                //     'destination' => BASE_PATH . '/public/vendor/web-tinker',
                // ],
            ],
        ];
    }
}
