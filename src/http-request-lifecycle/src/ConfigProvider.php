<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\RequestLifeCycle;

class_exists(Events\RequestReceived::class);
class_exists(Events\RequestHandled::class);
class_exists(Events\RequestTerminated::class);
class ConfigProvider
{
    public function __invoke(): array
    {
        return [];
    }
}
