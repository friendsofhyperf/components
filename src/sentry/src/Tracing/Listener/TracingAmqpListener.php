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
use Hyperf\Amqp\Event\AfterConsume;
use Hyperf\Amqp\Event\BeforeConsume;
use Hyperf\Amqp\Event\FailToConsume;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;

class TracingAmqpListener implements ListenerInterface
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
        $message = $event->getMessage();
        $context = new TransactionContext();
        $context->setName($message::class);
        $context->setSource(TransactionSource::custom());
        $context->setOp('amqp.consume');
        $context->setDescription('message:' . $message::class);
        $context->setStartTimestamp(microtime(true));

        $tags = [];

        if ($this->tagManager->has('amqp.type')) {
            $tags[$this->tagManager->get('amqp.type')] = $message->getType();
        }
        if ($this->tagManager->has('amqp.exchange')) {
            $tags[$this->tagManager->get('amqp.exchange')] = $message->getExchange();
        }
        if ($this->tagManager->has('amqp.routing_key')) {
            $tags[$this->tagManager->get('amqp.routing_key')] = $message->getRoutingKey();
        }
        if ($this->tagManager->has('amqp.poo_name')) {
            $tags[$this->tagManager->get('amqp.poo_name')] = $message->getPoolName();
        }
        if ($this->tagManager->has('amqp.queue') && method_exists($message, 'getQueue')) {
            $tags[$this->tagManager->get('amqp.queue')] = $message->getQueue();
        }

        $transaction = $sentry->startTransaction($context);

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction->getSampled()) {
            return;
        }

        $transaction->setTags($tags);

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

        if ($this->tagManager->has('amqp.result') && method_exists($event, 'getResult')) {
            $tags[$this->tagManager->get('amqp.result')] = $event->getResult();
        }

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('amqp.exception.stack_trace')) {
                $data[$this->tagManager->get('amqp.exception.stack_trace')] = (string) $exception;
            }
        }

        $transaction->setData($data);
        $transaction->setTags($tags);

        $transaction->finish(microtime(true));
    }
}
