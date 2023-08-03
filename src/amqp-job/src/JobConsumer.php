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

use FriendsOfHyperf\AmqpJob\Contract\Attempt;
use FriendsOfHyperf\AmqpJob\Contract\Packer;
use FriendsOfHyperf\AmqpJob\Contract\ShouldQueue;
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
            $ack = $data->handle();
            $this->clearAttempts($data);

            if ($ack instanceof Result) {
                return $ack;
            }

            return Result::tryFrom((string) $ack) ?? Result::ACK;
        } catch (Throwable $e) {
            if ($this->attempts($data)) {
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

    private function attempts(ShouldQueue $job): bool
    {
        $attempts = (int) $this->getAttempt()->incr($job->getJobId());

        if ($job->getMaxAttempts() > $attempts) {
            return true;
        }

        return false;
    }

    private function clearAttempts(ShouldQueue $job): void
    {
        $this->getAttempt()->clear($job->getJobId());
    }

    private function getAttempt(): Attempt
    {
        return $this->getContainer()->get(Attempt::class);
    }
}
