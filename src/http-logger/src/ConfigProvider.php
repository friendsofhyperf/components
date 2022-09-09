<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\HttpLogger;

use FriendsOfHyperf\HttpLogger\Profile\LogProfile;
use FriendsOfHyperf\HttpLogger\Profile\ProfileFactory;
use FriendsOfHyperf\HttpLogger\Writer\LogWriter;
use FriendsOfHyperf\HttpLogger\Writer\WriterFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                LogProfile::class => ProfileFactory::class,
                LogWriter::class => WriterFactory::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of http-logger.',
                    'source' => __DIR__ . '/../publish/http_logger.php',
                    'destination' => BASE_PATH . '/config/autoload/http_logger.php',
                ],
            ],
        ];
    }
}
