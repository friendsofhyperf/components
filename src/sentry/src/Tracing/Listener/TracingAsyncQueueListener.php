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
use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;

class TracingAsyncQueueListener implements ListenerInterface
{
    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
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
     * @param BeforeHandle|AfterHandle $event
     */
    public function process(object $event): void
    {
        $sentry = SentrySdk::init();

        match ($event::class) {
            BeforeHandle::class => $this->startTransaction($sentry, $event),
            RetryHandle::class, FailedHandle::class, AfterHandle::class => $this->finishTransaction($event),
        };
    }

    protected function startTransaction(HubInterface $sentry, BeforeHandle $event): void
    {
        $job = $event->getMessage()->job();
        $context = new TransactionContext();
        $context->setName($job::class);
        $context->setSource(TransactionSource::custom());
        $context->setOp('async_queue.job.handle');
        $context->setDescription('job:' . $job::class);
        $context->setStartTimestamp(microtime(true));

        $transaction = $sentry->startTransaction($context);
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

        $transaction->finish(microtime(true));
    }
}
