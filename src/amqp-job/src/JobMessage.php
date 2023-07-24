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

use Hyperf\Amqp\Message\ProducerMessage;

class JobMessage extends ProducerMessage
{
    public function __construct($payload, ?string $exchange = null, string|array|null $routingKey = null, ?string $pool = null)
    {
        if ($routingKey !== null) {
            $this->routingKey = $routingKey;
        }
        if ($pool !== null) {
            $this->poolName = $pool;
        }
        if ($exchange !== null) {
            $this->setExchange($exchange);
        }
        $this->setPayload($payload);
    }
}
