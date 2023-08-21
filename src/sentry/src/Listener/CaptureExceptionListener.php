<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use Hyperf\Amqp\Event as AmqpEvent;
use Hyperf\AsyncQueue\Event as AsyncQueueEvent;
use Hyperf\Command\Event as CommandEvent;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Event as CrontabEvent;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event as RequestEvent;
use Hyperf\Kafka\Event as KafkaEvent;
use Psr\Container\ContainerInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Throwable;

use function Hyperf\Support\make;

class CaptureExceptionListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            // request
            RequestEvent\RequestReceived::class,
            RequestEvent\RequestTerminated::class,
            // amqp
            AmqpEvent\BeforeConsume::class,
            AmqpEvent\FailToConsume::class,
            // command
            CommandEvent\BeforeHandle::class,
            CommandEvent\FailToHandle::class,
            // crontab
            CrontabEvent\BeforeExecute::class,
            CrontabEvent\FailToExecute::class,
            // async-queue
            AsyncQueueEvent\BeforeHandle::class,
            AsyncQueueEvent\FailedHandle::class,
            // kafka
            KafkaEvent\BeforeConsume::class,
            KafkaEvent\FailToConsume::class,
        ];
    }

    /**
     * @param RequestEvent\RequestTerminated|AmqpEvent\FailToConsume|CommandEvent\FailToHandle|AsyncQueueEvent\FailedHandle|KafkaEvent\FailToConsume|CrontabEvent\FailToExecute $event
     */
    public function process(object $event): void
    {
        $throwable = match ($event::class) {
            RequestEvent\RequestTerminated::class => $event->exception,
            AmqpEvent\FailToConsume::class, CommandEvent\FailToHandle::class, AsyncQueueEvent\FailedHandle::class, KafkaEvent\FailToConsume::class => $event->getThrowable(),
            CrontabEvent\FailToExecute::class => $event->throwable,
            default => SentrySdk::setCurrentHub(make(HubInterface::class)),
        };

        if (! $throwable instanceof Throwable) {
            return;
        }

        $this->captureException($throwable);
    }

    protected function captureException(Throwable $throwable): void
    {
        $hub = SentrySdk::getCurrentHub();

        try {
            $hub->captureException($throwable);
        } catch (Throwable $e) {
            $this->container->get(StdoutLoggerInterface::class)->error((string) $e);
        } finally {
            $hub->getClient()?->flush();
        }
    }
}
