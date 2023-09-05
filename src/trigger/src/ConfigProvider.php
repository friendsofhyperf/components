<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'aspects' => [
                Aspect\BinaryDataReaderAspect::class, // Fix MySQLReplication bug
            ],
            'dependencies' => [
                Mutex\ServerMutexInterface::class => Mutex\RedisServerMutex::class,
                Snapshot\BinLogCurrentSnapshotInterface::class => Snapshot\RedisBinLogCurrentSnapshot::class,
            ],
            'commands' => [
                Command\ConsumeCommand::class,
                Command\SubscribersCommand::class,
                Command\TriggersCommand::class,
            ],
            'listeners' => [
                // Listener\BindTriggerProcessesListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file of trigger.',
                    'source' => __DIR__ . '/../publish/trigger.php',
                    'destination' => BASE_PATH . '/config/autoload/trigger.php',
                ],
            ],
        ];
    }
}
