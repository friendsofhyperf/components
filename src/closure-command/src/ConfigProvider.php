<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ClosureCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        class_exists(\FriendsOfHyperf\ClosureCommand\Annotation\Command::class);
        class_exists(\FriendsOfHyperf\ClosureCommand\Console::class);
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The console route file of hyperf/command.',
                    'source' => __DIR__ . '/../../../hyperf/command/publish/console.php',
                    'destination' => \Hyperf\Command\Console::ROUTE,
                ],
            ],
        ];
    }
}
