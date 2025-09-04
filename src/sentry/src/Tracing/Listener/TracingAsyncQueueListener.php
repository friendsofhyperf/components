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
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;

class TracingAsyncQueueListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        protected Switcher $switcher
    ) {
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
            AfterHandle::class,
            RetryHandle::class,
            FailedHandle::class,
        ];
    }

    /**
     * @param BeforeHandle|AfterHandle|object $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isTracingEnable('async_queue')) {
            return;
        }

        match ($event::class) {
            BeforeHandle::class => $this->startTransaction($event),
            RetryHandle::class, FailedHandle::class, AfterHandle::class => $this->finishTransaction($event),
            default => null,
        };
    }

    protected function startTransaction(BeforeHandle $event): void
    {
        /** @var Carrier|null $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER, null, Coroutine::parentId());

        $job = $event->getMessage()->job();

        $this->continueTrace(
            sentryTrace: $carrier?->getSentryTrace() ?? '',
            baggage: $carrier?->getBaggage() ?? '',
            name: $job::class,
            op: 'queue.process',
            description: 'async_queue: ' . $job::class,
            source: TransactionSource::custom()
        )->setStartTimestamp(microtime(true));
    }

    protected function finishTransaction(AfterHandle|RetryHandle|FailedHandle $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        /** @var Carrier|null $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER, null, Coroutine::parentId());
        $data = [
            'messaging.system' => 'async_queue',
            'messaging.operation' => 'process',
            'messaging.message.id' => $carrier?->get('message_id'),
            'messaging.message.body.size' => $carrier?->get('body_size'),
            'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
            'messaging.message.retry.count' => $event->getMessage()->getAttempts(),
            'messaging.destination.name' => $carrier?->get('destination_name') ?? 'unknown queue',
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

        $transaction->setOrigin('auto.queue')->setData($data)->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }
}
