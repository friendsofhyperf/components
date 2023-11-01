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
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
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
            AfterExecute::class,
            BeforeExecute::class,
        ];
    }

    /**
     * @param BeforeExecute|AfterExecute $event
     */
    public function process(object $event): void
    {
        $sentry = SentrySdk::init();

        match ($event::class) {
            BeforeExecute::class => $this->startTransaction($sentry, $event),
            AfterExecute::class => $this->finishTransaction($event),
        };
    }

    protected function startTransaction(HubInterface $sentry, BeforeExecute $event): void
    {
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
        TraceContext::setTransaction($transaction);
        $sentry->setSpan($transaction);
        TraceContext::setSpan($transaction);
    }

    protected function finishTransaction(AfterExecute $event): void
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
