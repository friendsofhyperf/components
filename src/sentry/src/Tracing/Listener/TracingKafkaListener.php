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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
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
        protected Switcher $switcher,
        protected TagManager $tagManager,
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
        $sentryTrace = $baggage = '';

        if ($message instanceof ConsumeMessage) {
            foreach ($message->getHeaders() as $header) {
                if ($header->getHeaderKey() === Constants::TRACE_CARRIER) {
                    [$sentryTrace, $baggage] = $this->packer->unpack($header->getValue());
                    break;
                }
            }
        }

        $this->continueTrace(
            sentryTrace: $sentryTrace,
            baggage: $baggage,
            name: $consumer::class,
            op: 'kafka.consume',
            description: 'consumer: ' . $consumer::class,
            source: TransactionSource::custom()
        );
    }

    protected function finishTransaction(AfterConsume|FailToConsume $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        $consumer = $event->getConsumer();

        $tags = [];
        $data = [];

        if ($this->tagManager->has('kafka.topic')) {
            $tags[$this->tagManager->get('kafka.topic')] = $consumer->getTopic();
        }
        if ($this->tagManager->has('kafka.group_id')) {
            $tags[$this->tagManager->get('kafka.group_id')] = $consumer->getGroupId();
        }
        if ($this->tagManager->has('kafka.pool')) {
            $tags[$this->tagManager->get('kafka.pool')] = (string) $consumer->getPool();
        }

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('kafka.exception.stack_trace')) {
                $data[$this->tagManager->get('kafka.exception.stack_trace')] = (string) $exception;
            }
        }

        $transaction->setData($data);
        $transaction->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish(microtime(true));
    }
}
