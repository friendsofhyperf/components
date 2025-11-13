<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Helpers;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;

class PendingAmqpProducerMessageDispatch
{
    use Conditionable;

    public string $pool = 'default';

    public int $timeout = 5;

    public bool $confirm = false;

    public function __construct(protected ProducerMessageInterface $message)
    {
    }

    public function __destruct()
    {
        ApplicationContext::getContainer()
            ->get(Producer::class)
            ->produce($this->message, $this->confirm, $this->timeout);
    }

    public function onPool(string $pool): static
    {
        $this->pool = $pool;
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
