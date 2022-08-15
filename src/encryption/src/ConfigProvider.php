<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption;

use Friendsofhyperf\Encryption\Listener\BootEncryptionListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        // fix for IDE
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            // 'annotations' => [
            //     'scan' => [
            //         'paths' => [
            //             __DIR__,
            //         ],
            //     ],
            // ],
            'dependencies' => [
                Encrypter::class => EncrypterFactory::class,
            ],
            'listeners' => [
                BootEncryptionListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for Encryption.',
                    'source' => __DIR__ . '/../publish/encryption.php',
                    'destination' => BASE_PATH . '/config/autoload/encryption.php',
                ],
            ],
        ];
    }
}
