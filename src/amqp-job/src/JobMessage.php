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

use FriendsOfHyperf\AmqpJob\Contract\JobInterface;
use FriendsOfHyperf\AmqpJob\Contract\Packer;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Context\ApplicationContext;

class JobMessage extends ProducerMessage
{
    public function __construct(JobInterface $payload)
    {
        if (! $payload->getJobId()) {
            $payload->setJobId(uniqid('', true));
        }
        $this->setPayload($payload);
    }

    final public function serialize(): string
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);
        return $packer->pack($this->payload);
    }
}
