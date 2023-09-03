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

class_alias(\Hyperf\Command\Console::class, Console::class);

if (! class_exists(Console::class)) {
    /**
     * @deprecated since 3.1, use Hyperf\Command\Console instead
     * @mixin \Hyperf\Command\Console
     */
    class Console
    {
    }
}
