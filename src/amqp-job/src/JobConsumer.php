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
use Hyperf\Context\ApplicationContext;
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
            $data->clearAttempts($data->getJobId());

            return Result::ACK;
        } catch (Throwable $e) {
            if ($data->attempts()) {
                return Result::REQUEUE;
            }

            ApplicationContext::getContainer()->get(StdoutLoggerInterface::class)->error((string) $e);
            return Result::DROP;
        }
    }

    final public function unserialize(string $data)
    {
        $container = ApplicationContext::getContainer();
        $packer = $container->get(Packer::class);

        return $packer->unpack($data);
    }
}
