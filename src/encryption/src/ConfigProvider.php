<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Encryption;

use FriendsOfHyperf\Encryption\Listener\BootEncryptionListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Encrypter::class => EncrypterFactory::class,
            ],
            'listeners' => [
                BootEncryptionListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file for encryption.',
                    'source' => __DIR__ . '/../publish/encryption.php',
                    'destination' => BASE_PATH . '/config/autoload/encryption.php',
                ],
            ],
        ];
    }
}
