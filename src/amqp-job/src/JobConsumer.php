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
        if (! $data instanceof Job) {
            return Result::DROP;
        }

        try {
            $data->handle();
            $data->clearAttempts();

            return Result::ACK;
        } catch (Throwable $e) {
            if ($data->attempts()) {
                return Result::REQUEUE;
            }

            $logger = $this->getContainer()->get(StdoutLoggerInterface::class);
            $logger->error((string) $e);

            return Result::DROP;
        }
    }

    final public function unserialize(string $data)
    {
        $packer = $this->getContainer()->get(Packer::class);

        return $packer->unpack($data);
    }
}
