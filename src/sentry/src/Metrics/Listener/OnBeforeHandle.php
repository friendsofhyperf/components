<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics\Listener;

use FriendsOfHyperf\Sentry\Constants;
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Context\Context;
use Hyperf\Event\Contract\ListenerInterface;

class OnBeforeHandle implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BeforeHandle::class,
            AfterExecute::class,
        ];
    }

    /**
     * @param object|BeforeHandle|AfterExecute $event
     */
    public function process(object $event): void
    {
        match (true) {
            $event instanceof BeforeHandle => Context::set(Constants::RUN_IN_COMMAND, true),
            $event instanceof AfterExecute => Context::destroy(Constants::RUN_IN_COMMAND),
            default => null,
        };
    }
}
