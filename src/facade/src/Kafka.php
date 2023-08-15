<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\Kafka\Exception\ConnectionClosedException;
use Hyperf\Kafka\Exception\TimeoutException;
use Hyperf\Kafka\ProducerManager as Accessor;
use longlang\phpkafka\Producer\ProduceMessage;

/**
 * @mixin Accessor
 */
class Kafka extends Facade
{
    /**
     * @param string $queue
     * @throws ConnectionClosedException
     * @throws TimeoutException
     */
    public static function send(ProduceMessage $produceMessage, $queue = 'default')
    {
        return self::getProducer($queue)->sendBatch([$produceMessage]);
    }

    /**
     * @param ProduceMessage[] $produceMessages
     * @param string $queue
     * @throws ConnectionClosedException
     * @throws TimeoutException
     */
    public static function sendBatch($produceMessages, $queue = 'default')
    {
        return self::getProducer($queue)->sendBatch($produceMessages);
    }

    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
