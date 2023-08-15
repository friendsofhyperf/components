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

function dispatch(JobInterface $payload, ?string $exchange = null, string|array|null $routingKey = null, ?string $pool = null, ?bool $confirm = null, ?int $timeout = null): bool
{
    $message = new JobMessage($payload);
    $exchange = $exchange ?? $payload->getExchange();
    $routingKey = $routingKey ?? $payload->getRoutingKey();
    $poolName = $pool ?? $payload->getPoolName();

    if ($exchange !== null) {
        $message->setExchange($exchange);
    }
    if ($routingKey !== null) {
        $message->setRoutingKey($routingKey);
    }
    if ($poolName !== null) {
        $message->setPoolName($poolName);
    }

    return ApplicationContext::getContainer()->get(Producer::class)->produce(
        $message,
        $confirm ?? $payload->getConfirm(),
        $timeout ?? $payload->getTimeout()
    );
}
