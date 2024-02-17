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
        protected Switcher $switcher,
        protected TagManager $tagManager,
        protected CarrierPacker $packer
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
        $sentryTrace = $baggage = '';

        /** @var string|null $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER, null, Coroutine::parentId());

        if ($carrier) {
            [$sentryTrace, $baggage] = $this->packer->unpack($carrier);
        }

        $job = $event->getMessage()->job();

        $this->continueTrace(
            sentryTrace: $sentryTrace,
            baggage: $baggage,
            name: $job::class,
            op: 'async_queue.job.process',
            description: 'job:' . $job::class,
            source: TransactionSource::custom()
        );
    }

    protected function finishTransaction(AfterHandle|RetryHandle|FailedHandle $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction || ! $transaction->getSampled()) {
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
            if ($this->tagManager->has('async_queue.exception.stack_trace')) {
                $data[$this->tagManager->get('async_queue.exception.stack_trace')] = (string) $exception;
            }
        }

        $transaction->setData($data);
        $transaction->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish(microtime(true));
    }
}
