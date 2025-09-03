<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Listener;

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
use Hyperf\Amqp\Event\AfterConsume;
use Hyperf\Amqp\Event\BeforeConsume;
use Hyperf\Amqp\Event\FailToConsume;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Context\Context;
use Hyperf\Event\Contract\ListenerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;

class TracingAmqpListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        protected Switcher $switcher,
        protected CarrierPacker $packer
    ) {
    }

    public function listen(): array
    {
        return [
            BeforeConsume::class,
            AfterConsume::class,
            FailToConsume::class,
        ];
    }

    /**
     * @param BeforeConsume|AfterConsume|FailToConsume|object $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isTracingEnable('amqp')) {
            return;
        }

        match ($event::class) {
            BeforeConsume::class => $this->startTransaction($event),
            AfterConsume::class, FailToConsume::class => $this->finishTransaction($event),
            default => null
        };
    }

    protected function startTransaction(BeforeConsume $event): void
    {
        $message = $event->getMessage();
        $sentryTrace = $baggage = '';

        if (method_exists($event, 'getAMQPMessage')) {
            /** @var AMQPMessage $amqpMessage */
            $amqpMessage = $event->getAMQPMessage();
            /** @var AMQPTable|null $applicationHeaders */
            $applicationHeaders = $amqpMessage->has('application_headers') ? $amqpMessage->get('application_headers') : null;
            if ($applicationHeaders && isset($applicationHeaders[Constants::TRACE_CARRIER])) {
                [$sentryTrace, $baggage] = $this->packer->unpack($applicationHeaders[Constants::TRACE_CARRIER]);
                Context::set(Constants::TRACE_CARRIER, $applicationHeaders[Constants::TRACE_CARRIER]);
            }
        }

        $this->continueTrace(
            sentryTrace: $sentryTrace,
            baggage: $baggage,
            name: $message::class,
            op: 'topic.process',
            description: $message::class,
            source: TransactionSource::custom()
        )->setStartTimestamp(microtime(true));
    }

    protected function finishTransaction(AfterConsume|FailToConsume $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        $payload = [];
        if ($carrier = Context::get(Constants::TRACE_CARRIER)) {
            $payload = json_decode((string) $carrier, true);
        }

        /** @var ConsumerMessage $message */
        $message = $event->getMessage();
        $data = [
            'messaging.system' => 'amqp',
            'messaging.operation' => 'process',
            'messaging.message.id' => $payload['message_id'] ?? null,
            'messaging.message.body.size' => $payload['body_size'] ?? null,
            'messaging.message.receive.latency' => isset($payload['publish_time']) ? (microtime(true) - $payload['publish_time']) : null,
            'messaging.message.retry.count' => 0,
            'messaging.destination.name' => $payload['destination_name'] ?? $message->getExchange(),
            // for amqp
            'messaging.amqp.message.type' => $message->getTypeString(),
            'messaging.amqp.message.routing_key' => $message->getRoutingKey(),
            'messaging.amqp.message.exchange' => $message->getExchange(),
            'messaging.amqp.message.queue' => $message->getQueue(),
            'messaging.amqp.message.pool_name' => $message->getPoolName(),
            'messaging.amqp.message.result' => $event instanceof AfterConsume ? $event->getResult()->value : 'fail',
        ];
        $tags = [];

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }
        }

        $transaction->setOrigin('auto.amqp')->setData($data)->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }
}
