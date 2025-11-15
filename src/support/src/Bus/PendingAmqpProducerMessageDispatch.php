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

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;

/**
 * @property array{application_headers?:AMQPTable} $properties
 */
class PendingAmqpProducerMessageDispatch
{
    use Conditionable;

    protected ?string $pool = null;

    protected int $timeout = 5;

    protected bool $confirm = false;

    protected null|array|string $routingKey = null;

    protected ?string $exchange = null;

    public function __construct(protected ProducerMessageInterface $message)
    {
    }

    public function __destruct()
    {
        $this->pool && $this->message->setPoolName($this->pool);
        $this->routingKey && $this->message->setRoutingKey($this->routingKey);
        $this->exchange && $this->message->setExchange($this->exchange);
        ApplicationContext::getContainer()
            ->get(Producer::class)
            ->produce($this->message, $this->confirm, $this->timeout);
    }

    public function onPool(string $pool): static
    {
        $this->pool = $pool;
        return $this;
    }

    public function setPayload(mixed $data): static
    {
        $this->message->setPayload($data);
        return $this;
    }

    public function withHeader(string $key, mixed $value, ?int $ttl = null): static
    {
        (function () use ($key, $value, $ttl) {
            $this->properties['application_headers'] ??= new \PhpAmqpLib\Wire\AMQPTable(); // @phpstan-ignore-line
            $this->properties['application_headers']->set($key, $value, $ttl);
        })->call($this->message);
        return $this;
    }

    public function setExchange(string $exchange): static
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function setRoutingKey(array|string $routingKey): static
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    public function setConfirm(bool $confirm): static
    {
        $this->confirm = $confirm;
        return $this;
    }

    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }
}
