<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\MiddlewarePlus;

use Hyperf\Dispatcher\AbstractRequestHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        AbstractRequestHandler::class => __DIR__ . '/../class_map/AbstractRequestHandler.php',
                    ],
                ],
            ],
        ];
    }
}
