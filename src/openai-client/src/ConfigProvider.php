<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\OpenAi;

use OpenAI\Client;
use OpenAI\Contracts\ClientContract;

final class ConfigProvider
{
    public function __invoke()
    {
        defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__, 2));

        return [
            'dependencies' => [
                Client::class => ClientFactory::class,
                ClientContract::class => fn ($container) => $container->get(Client::class), // alias for Client::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file for OpenAI.',
                    'source' => __DIR__ . '/../publish/openai.php',
                    'destination' => BASE_PATH . '/config/autoload/openai.php',
                ],
            ],
        ];
    }
}
