<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand;

use FriendsOfHyperf\ClosureCommand\Annotation\CommandCollector;
use FriendsOfHyperf\ClosureCommand\Listener\RegisterCommandListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'annotations' => [
                'scan' => [
                    'collectors' => [
                        CommandCollector::class,
                    ],
                ],
            ],
            'listeners' => [
                RegisterCommandListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The console route file of closure-command.',
                    'source' => __DIR__ . '/../publish/console.php',
                    'destination' => BASE_PATH . '/config/console.php',
                ],
            ],
        ];
    }
}
