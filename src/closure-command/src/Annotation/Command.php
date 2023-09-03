<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ClosureCommand\Annotation;

class_alias(\Hyperf\Command\Annotation\AsCommand::class, Command::class);

if (! class_exists(Command::class)) {
    /**
     * @deprecated since 3.1, use Hyperf\Command\Annotation\AsCommand instead
     * @mixin \Hyperf\Command\Annotation\AsCommand
     */
    class Command
    {
    }
}
