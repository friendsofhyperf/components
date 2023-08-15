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
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use Hyperf\Contract\StdoutLoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

abstract class JobConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): Result
    {
        $logger = $this->getContainer()->get(StdoutLoggerInterface::class);

        if (! $data instanceof JobInterface) {
            $logger->error(sprintf('The message is not an instance of %s.', JobInterface::class));

            return Result::DROP;
        }

        try {
            $ack = $data->handle();

            if ($ack instanceof Result) {
                return $ack;
            }

            return Result::tryFrom((string) $ack) ?? Result::ACK;
        } catch (Throwable $e) {
            $logger->error((string) $e);

            if ($data->attempts()) {
                return Result::REQUEUE;
            }

            return Result::DROP;
        }
    }

    final public function unserialize(string $data)
    {
        $packer = $this->getContainer()->get(Packer::class);

        return $packer->unpack($data);
    }
}
