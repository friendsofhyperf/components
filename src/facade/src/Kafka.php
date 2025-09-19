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

use Hyperf\Kafka\ProducerManager;
use longlang\phpkafka\Producer\ProduceMessage;
use Override;

/**
 * @mixin ProducerManager
 * @property null|string $queue
 */
class Kafka extends Facade
{
    public static function send(ProduceMessage $produceMessage, ?string $queue = null): void
    {
        $queue = (fn ($queue) => $this->queue ?? $queue)->call($produceMessage, $queue);
        self::getProducer($queue)->sendBatch([$produceMessage]);
    }

    /**
     * @param ProduceMessage[] $produceMessages
     */
    public static function sendBatch($produceMessages, ?string $queue = null): void
    {
        /** @var array<string,ProduceMessage[]> */
        $groupMessages = [];
        $queue ??= 'default';

        foreach ($produceMessages as $message) {
            $queue = (fn ($queue) => $this->queue ?? $queue)->call($message, $queue);
            $groupMessages[$queue][] = $message;
        }

        foreach ($groupMessages as $queue => $messages) {
            self::getProducer($queue)->sendBatch($messages);
        }
    }

    #[Override]
    protected static function getFacadeAccessor()
    {
        return ProducerManager::class;
    }
}
