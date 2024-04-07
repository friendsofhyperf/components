<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Redis\Subscriber;

use FriendsOfHyperf\Redis\Subscriber\Exception\SocketException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Throwable;

class CommandInvoker
{
    protected Channel $resultChannel;

    protected Channel $messageChannel;

    private Channel $pingChannel;

    private Timer $timer;

    public function __construct(protected Connection $connection, protected ?StdoutLoggerInterface $logger = null)
    {
        $this->connection = $connection;
        $this->resultChannel = new Channel();
        $this->pingChannel = new Channel();
        $this->messageChannel = new Channel(100);
        $this->timer = new Timer();
        Coroutine::create(function () use ($connection) {
            $this->receive($connection);
        });
    }

    /**
     * @throws SocketException
     */
    public function receive(Connection $connection)
    {
        $buffer = null;

        while (true) {
            $line = $connection->recv();

            if ($line === false || $line === '') {
                $this->interrupt();
                break;
            }

            $line = substr($line, 0, -strlen(Constants::CRLF));

            if ($line == '+OK') {
                $this->resultChannel->push($line);
                continue;
            }

            if ($line == '*3') {
                if (! empty($buffer)) {
                    $this->resultChannel->push($buffer);
                    $buffer = null;
                }
                $buffer[] = $line;
                continue;
            }

            $buffer[] = $line;
            $type = $buffer[2] ?? false;

            if ($type == 'subscribe' && count($buffer) == 6) {
                $this->resultChannel->push($buffer);
                $buffer = null;
                continue;
            }

            if ($type == 'unsubscribe' && count($buffer) == 6) {
                $this->resultChannel->push($buffer);
                $buffer = null;
                continue;
            }

            if ($type == 'message' && count($buffer) == 7) {
                $message = new Message();
                $message->channel = $buffer[4];
                $message->payload = $buffer[6];
                $timerID = $this->timer->after(30, function () use ($message) {
                    $this->logger?->error(sprintf('Message channel (%s) is 30 seconds full, disconnected', $message->channel));
                    $this->interrupt();
                });
                $this->messageChannel->push($message);
                $this->timer->clear($timerID);
                $buffer = null;
                continue;
            }

            if ($type == 'psubscribe' && count($buffer) == 6) {
                $this->resultChannel->push($buffer);
                $buffer = null;
                continue;
            }

            if ($type == 'punsubscribe' && count($buffer) == 6) {
                $this->resultChannel->push($buffer);
                $buffer = null;
                continue;
            }

            if ($type == 'pmessage' && count($buffer) == 9) {
                $message = new Message();
                $message->pattern = $buffer[4];
                $message->channel = $buffer[6];
                $message->payload = $buffer[8];
                $timerID = $this->timer->after(30, function () use ($message) {
                    $this->logger?->error(sprintf('Message channel (%s) is 30 seconds full, disconnected', $message->channel));
                    $this->interrupt();
                });
                $this->messageChannel->push($message);
                $this->timer->clear($timerID);
                $buffer = null;
                continue;
            }

            if ($type == 'pong' && count($buffer) == 5) {
                $this->pingChannel->push('pong');
                $buffer = null;
                continue;
            }
        }
    }

    /**
     * @param int|string|array<mixed>|null $command
     */
    public function invoke(mixed $command, int $number): array
    {
        try {
            $this->connection->send(CommandBuilder::build($command));
        } catch (Throwable $e) {
            $this->interrupt();
            throw $e;
        }
        $result = [];
        for ($i = 0; $i < $number; ++$i) {
            $result[] = $this->resultChannel->pop();
        }
        return $result;
    }

    public function channel(): Channel
    {
        return $this->messageChannel;
    }

    public function interrupt(): bool
    {
        $this->connection->close();
        $this->resultChannel->close();
        $this->messageChannel->close();
        return true;
    }

    /**
     * @return string
     */
    public function ping(int $timeout = 1)
    {
        $this->connection->send(CommandBuilder::build('ping'));
        return $this->pingChannel->pop($timeout);
    }
}
