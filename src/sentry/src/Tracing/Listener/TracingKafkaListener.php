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

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Kafka\Event\AfterConsume;
use Hyperf\Kafka\Event\BeforeConsume;
use Hyperf\Kafka\Event\FailToConsume;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;

class TracingKafkaListener implements ListenerInterface
{
    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
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
     * @param BeforeConsume|AfterConsume|FailToConsume| $event
     */
    public function process(object $event): void
    {
        match ($event::class) {
            BeforeConsume::class => $this->startTransaction($event),
            AfterConsume::class, FailToConsume::class => $this->finishTransaction($event),
        };
    }

    protected function startTransaction(BeforeConsume $event): void
    {
        $sentry = SentrySdk::init();
        $consumer = $event->getConsumer();
        $context = new TransactionContext();
        $context->setName($consumer->getName());
        $context->setSource(TransactionSource::custom());
        $context->setOp('kafka.consume');
        $context->setDescription($consumer::class);
        $context->setStartTimestamp(microtime(true));

        $transaction = $sentry->startTransaction($context);

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction->getSampled()) {
            return;
        }

        TraceContext::setTransaction($transaction);
        $sentry->setSpan($transaction);
        TraceContext::setSpan($transaction);
    }

    protected function finishTransaction(AfterConsume|FailToConsume $event): void
    {
        $transaction = TraceContext::getTransaction();

        if (! $transaction) {
            return;
        }

        $data = [];
        $tags = [];

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

        $transaction->finish(microtime(true));
    }
}
