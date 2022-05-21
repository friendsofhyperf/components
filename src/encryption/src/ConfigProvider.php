<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/encryption.
 *
 * @link     https://github.com/friendsofhyperf/encryption
 * @document https://github.com/friendsofhyperf/encryption/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Friendsofhyperf\Encryption;

class ConfigProvider
{
    public function __invoke(): array
    {
        // fix for IDE
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'dependencies' => [
                Encrypter::class => EncrypterFactory::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
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
