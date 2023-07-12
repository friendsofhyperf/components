<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\EasyCommand;

use FriendsOfHyperf\EasyCommand\Annotation\CommandCollector;
use FriendsOfHyperf\EasyCommand\Listener\RegisterCommandListener;

class ConfigProvider
{
    public function __invoke(): array
    {
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
        ];
    }
}
