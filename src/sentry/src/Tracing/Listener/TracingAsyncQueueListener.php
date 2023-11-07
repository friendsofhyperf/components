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
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;

class TracingAsyncQueueListener implements ListenerInterface
{
    public function __construct(protected Switcher $switcher)
    {
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
     * @param BeforeHandle|AfterHandle $event
     */
    public function process(object $event): void
    {
        match ($event::class) {
            BeforeHandle::class => $this->startTransaction($event),
            RetryHandle::class, FailedHandle::class, AfterHandle::class => $this->finishTransaction($event),
        };
    }

    protected function startTransaction(BeforeHandle $event): void
    {
        $sentry = SentrySdk::init();
        $job = $event->getMessage()->job();

        $context = new TransactionContext();
        $context->setName($job::class);
        $context->setSource(TransactionSource::custom());
        $context->setOp('async_queue.job.handle');
        $context->setDescription('job:' . $job::class);
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

    protected function finishTransaction(AfterHandle|RetryHandle|FailedHandle $event): void
    {
        $transaction = TraceContext::getTransaction();

        if (! $transaction) {
            return;
        }

        $data = [];
        $tags = [];
        $status = SpanStatus::ok();

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $status = SpanStatus::internalError();
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            $data['async_queue.exception.stack_trace'] = (string) $exception;
        }

        $transaction->setData($data);
        $transaction->setTags($tags);
        $transaction->setStatus($status);
        $transaction->finish(microtime(true));
    }
}
