<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\Amqp;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;

use function FriendsOfHyperf\Helpers\di;

function dispatch(ProducerMessageInterface $job, bool $confirm = false, int $timeout = 5): bool
{
    return di(Producer::class)->produce($job, $confirm, $timeout);
}
