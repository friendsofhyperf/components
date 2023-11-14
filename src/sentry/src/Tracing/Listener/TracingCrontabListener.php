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
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Crontab\Event\AfterExecute;
use Hyperf\Crontab\Event\BeforeExecute;
use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;

class TracingCrontabListener implements ListenerInterface
{
    use SpanStarter;

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
        $crontab = $event->crontab;

        $this->continueTrace(
            name: $crontab->getName() ?: '<unnamed crontab>',
            op: 'crontab.execute',
            description: $crontab->getMemo(),
            source: TransactionSource::task()
        );
    }

    protected function finishTransaction(AfterExecute|FailToExecute $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        $crontab = $event->crontab;
        $data = [];
        $tags = [];

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

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish(microtime(true));
    }
}
