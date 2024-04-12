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

    public function __construct(protected Switcher $switcher)
    {
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
     * @param BeforeExecute|AfterExecute|FailToExecute|object $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isTracingEnable('crontab')) {
            return;
        }

        match ($event::class) {
            BeforeExecute::class => $this->startTransaction($event),
            AfterExecute::class, FailToExecute::class => $this->finishTransaction($event),
            default => null,
        };
    }

    protected function startTransaction(BeforeExecute $event): void
    {
        $crontab = $event->crontab;

        $this->continueTrace(
            name: $crontab->getName() ?: '<unnamed crontab>',
            op: 'crontab.run',
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
        $tags = [
            'crontab.rule' => $crontab->getRule(),
            'crontab.type' => $crontab->getType(),
            'crontab.options.is_single' => $crontab->isSingleton(),
            'crontab.options.is_on_one_server' => $crontab->isOnOneServer(),
        ];

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

        $transaction->setData($data);
        $transaction->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish(microtime(true));
    }
}
