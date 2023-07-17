<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\Kafka;

use Hyperf\Kafka\ProducerManager;
use longlang\phpkafka\Producer\ProduceMessage;

use function FriendsOfHyperf\Helpers\di;

function dispatch(ProduceMessage $job, string $name = 'default'): bool
{
    di(ProducerManager::class)
        ->getProducer($name)
        ->sendBatch([$job]);
    return true;
}
