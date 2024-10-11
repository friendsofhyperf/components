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

use FriendsOfHyperf\AmqpJob\Annotation\AmqpJob;
use FriendsOfHyperf\AmqpJob\Contract\JobInterface;
use Hyperf\Amqp\Producer;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;

function dispatch(
    JobInterface $payload,
    ?bool $confirm = null,
    ?int $timeout = null
): bool {
    $annotations = AnnotationCollector::getClassAnnotations(get_class($payload));
    $message = (new JobMessage($payload));
    foreach ($annotations as $annotation) {
        if ($annotation instanceof AmqpJob) {
            $message->setExchange($annotation->exchange);
            $message->setRoutingKey($annotation->exchange);
            $message->setPoolName($annotation->pool);
        }
    }

    return ApplicationContext::getContainer()
        ->get(Producer::class)
        ->produce(
            $message,
            $confirm ?? $payload->getConfirm(),
            $timeout ?? $payload->getTimeout()
        );
}
