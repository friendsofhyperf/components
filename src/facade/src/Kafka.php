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

use FriendsOfHyperf\Support\Bus\PendingKafkaProducerMessageDispatch;
use Hyperf\Kafka\ProducerManager;
use longlang\phpkafka\Producer\ProduceMessage;
use Override;

use function FriendsOfHyperf\Support\dispatch;

/**
 * @mixin ProducerManager
 */
class Kafka extends Facade
{
    public function dispatch(ProduceMessage $produceMessage): PendingKafkaProducerMessageDispatch
    {
        return dispatch($produceMessage);
    }

    public static function send(ProduceMessage $produceMessage, ?string $pool = null): void
    {
        self::getProducer($pool ?? 'default')->sendBatch([$produceMessage]);
    }

    /**
     * @param ProduceMessage[] $produceMessages
     */
    public static function sendBatch($produceMessages, ?string $pool = null): void
    {
        self::getProducer($pool ?? 'default')->sendBatch($produceMessages);
    }

    #[Override]
    protected static function getFacadeAccessor()
    {
        return ProducerManager::class;
    }
}
