<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Bus;

use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;
use Hyperf\Kafka\ProducerManager;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Protocol\RecordBatch\RecordHeader;

/**
 * @property array $headers
 * @property null|string $key
 * @property null|string $value
 */
class PendingKafkaProducerMessageDispatch
{
    use Conditionable;

    public ?string $pool = null;

    public function __construct(protected ProduceMessage $message)
    {
    }

    public function __destruct()
    {
        ApplicationContext::getContainer()
            ->get(ProducerManager::class)
            ->getProducer($this->pool ?? 'default')
            ->sendBatch([$this->message]);
    }

    public function onPool(string $pool): static
    {
        $this->pool = $pool;
        return $this;
    }

    public function setKey(string $key): static
    {
        (fn () => $this->key = $key)->call($this->message);
        return $this;
    }

    public function setValue(string $value): static
    {
        (fn () => $this->value = $value)->call($this->message);
        return $this;
    }

    public function withHeader(string $key, string $value): static
    {
        $header = (new RecordHeader())->setHeaderKey($key)->setValue($value);
        (fn () => $this->headers[] = $header)->call($this->message);
        return $this;
    }
}
