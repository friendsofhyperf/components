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
