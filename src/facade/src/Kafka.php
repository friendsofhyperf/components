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
 * @property null|string $queue
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
        /** @var array<string,ProduceMessage[]> */
        $groupMessages = [];

        foreach ($produceMessages as $message) {
            $subPool = (fn () => $pool ?? $this->pool ?? $this->queue ?? 'default')->call($message);
            $groupMessages[$subPool][] = $message;
        }

        foreach ($groupMessages as $subPool => $messages) {
            self::getProducer($subPool)->sendBatch($messages);
        }
    }

    #[Override]
    protected static function getFacadeAccessor()
    {
        return ProducerManager::class;
    }
}
