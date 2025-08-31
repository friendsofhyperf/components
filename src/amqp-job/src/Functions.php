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
 *
 * @param string|null $exchange deprecated since v3.1, will be removed in v3.2
 * @param string|array|null $routingKey deprecated since v3.1, will be removed in v3.2
 * @param string|null $pool deprecated since v3.1, will be removed in v3.2
 */
function dispatch(
    JobInterface $payload,
    ?string $exchange = null,
    string|array|null $routingKey = null,
    ?string $pool = null,
    ?bool $confirm = null,
    ?int $timeout = null
): bool {
    $message = (new JobMessage($payload))
        ->when(
            $exchange ?? $payload->getExchange(),
            fn (JobMessage $message, $exchange) => $message->setExchange($exchange)
        )
        ->when(
            $routingKey ?? $payload->getRoutingKey(),
            fn (JobMessage $message, $routingKey) => $message->setRoutingKey($routingKey)
        )
        ->when(
            $pool ?? $payload->getPoolName(),
            fn (JobMessage $message, $poolName) => $message->setPoolName($poolName)
        );

    return ApplicationContext::getContainer()
        ->get(Producer::class)
        ->produce(
            $message,
            $confirm ?? $payload->getConfirm(),
            $timeout ?? $payload->getTimeout()
        );
}
