<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelObserver;

use FriendsOfHyperf\ModelObserver\Listener\ObserverListener;
use FriendsOfHyperf\ModelObserver\Listener\RegisterObserverListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
                Command\GeneratorCommand::class,
            ],
            'listeners' => [
                ObserverListener::class,
                RegisterObserverListener::class,
            ],
        ];
    }
}
