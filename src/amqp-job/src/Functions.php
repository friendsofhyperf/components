<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob;

use FriendsOfHyperf\AmqpJob\Contract\JobInterface;
use Hyperf\Amqp\Producer;
use Hyperf\Context\ApplicationContext;

/**
 * Dispatch a job to a queue.
 */
function dispatch(
    JobInterface $payload,
    ?bool $confirm = null,
    ?int $timeout = null
): bool {
    $message = (new JobMessage($payload));

    return ApplicationContext::getContainer()
        ->get(Producer::class)
        ->produce(
            $message,
            $confirm ?? $payload->getConfirm(),
            $timeout ?? $payload->getTimeout()
        );
}
