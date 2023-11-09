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
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Event\AfterExecute;
use Hyperf\Crontab\Event\BeforeExecute;
use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;

class TracingCrontabListener implements ListenerInterface
{
    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function listen(): array
    {
        return [
            BeforeExecute::class,
            FailToExecute::class,
            AfterExecute::class,
        ];
    }

    /**
     * @param BeforeExecute|AfterExecute|FailToExecute $event
     */
    public function process(object $event): void
    {
        match ($event::class) {
            BeforeExecute::class => $this->startTransaction($event),
            AfterExecute::class, FailToExecute::class => $this->finishTransaction($event),
        };
    }

    protected function startTransaction(BeforeExecute $event): void
    {
        $sentry = SentrySdk::init();
        /** @var Crontab $crontab */
        $crontab = $event->crontab;
        $context = new TransactionContext();
        $context->setName($crontab->getName() ?: '<unnamed crontab>');
        $context->setSource(TransactionSource::custom());
        $context->setOp('crontab.execute');
        $context->setDescription($crontab->getMemo());
        $context->setStartTimestamp(microtime(true));

        $data = [];

        if ($this->tagManager->has('crontab.rule')) {
            $data[$this->tagManager->get('crontab.rule')] = $crontab->getRule();
        }
        if ($this->tagManager->has('crontab.type')) {
            $data[$this->tagManager->get('crontab.type')] = $crontab->getType();
        }
        if ($this->tagManager->has('crontab.options')) {
            $data[$this->tagManager->get('crontab.options')] = [
                'is_single' => $crontab->isSingleton(),
                'is_on_one_server' => $crontab->isOnOneServer(),
            ];
        }

        $context->setData($data);

        $transaction = $sentry->startTransaction($context);

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction->getSampled()) {
            return;
        }

        TraceContext::setTransaction($transaction);
        $sentry->setSpan($transaction);
        TraceContext::setSpan($transaction);
    }

    protected function finishTransaction(AfterExecute|FailToExecute $event): void
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
            if ($this->tagManager->has('crontab.exception.stack_trace')) {
                $data[$this->tagManager->get('crontab.exception.stack_trace')] = (string) $exception;
            }
        }

        $transaction->setData($data);
        $transaction->setTags($tags);

        $transaction->finish(microtime(true));
    }
}
