<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/exception-event.
 *
 * @link     https://github.com/friendsofhyperf/exception-event
 * @document https://github.com/friendsofhyperf/exception-event/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ExceptionEvent;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
            'listeners' => [],
            'publish' => [],
        ];
    }
}
