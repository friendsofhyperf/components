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
use FriendsOfHyperf\Sentry\Util\Carrier;
use Hyperf\Context\Context;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Kafka\Event\AfterConsume;
use Hyperf\Kafka\Event\BeforeConsume;
use Hyperf\Kafka\Event\FailToConsume;
use longlang\phpkafka\Consumer\ConsumeMessage;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;

class TracingKafkaListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        protected Switcher $switcher
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
        if (! $this->switcher->isTracingEnable('kafka')) {
            return;
        }

        match ($event::class) {
            BeforeConsume::class => $this->startTransaction($event),
            AfterConsume::class, FailToConsume::class => $this->finishTransaction($event),
            default => null,
        };
    }

    protected function startTransaction(BeforeConsume $event): void
    {
        $consumer = $event->getConsumer();
        $message = $event->getData();
        $carrier = null;

        if ($message instanceof ConsumeMessage) {
            foreach ($message->getHeaders() as $header) {
                if ($header->getHeaderKey() === Constants::TRACE_CARRIER) {
                    $carrier = Carrier::fromJson($header->getValue());
                    Context::set(Constants::TRACE_CARRIER, $carrier);
                    break;
                }
            }
        }

        $this->continueTrace(
            sentryTrace: $carrier?->getSentryTrace() ?? '',
            baggage: $carrier?->getBaggage() ?? '',
            name: $consumer->getTopic() . ' process',
            op: 'queue.process',
            description: $consumer::class,
            origin: 'auto.kafka',
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

        /** @var null|Carrier $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER);
        $consumer = $event->getConsumer();
        $tags = [];
        $data = [
            'messaging.system' => 'kafka',
            'messaging.operation' => 'process',
            'messaging.message.id' => $carrier?->get('message_id'),
            'messaging.message.body.size' => $carrier?->get('body_size'),
            'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
            'messaging.message.retry.count' => 0,
            'messaging.destination.name' => $carrier?->get('destination_name') ?: (is_array($consumer->getTopic()) ? implode(',', $consumer->getTopic()) : $consumer->getTopic()),
            // for kafka
            'messaging.kafka.consumer.group' => $consumer->getGroupId(),
            'messaging.kafka.consumer.pool' => $consumer->getPool(),
        ];

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

        $transaction->setData($data)->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }
}
