<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ExceptionEvent;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                Aspect\ExceptionHandlerDispatcherAspect::class,
            ],
        ];
    }
}
