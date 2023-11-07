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
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * @property int $exitCode
 * @property \Symfony\Component\Console\Input\InputInterface $input
 */
class TracingCommandListener implements ListenerInterface
{
    /**
     * @var string[]
     */
    protected array $ignoreCommands = [];

    public function __construct(ConfigInterface $config, protected Switcher $switcher)
    {
        $this->ignoreCommands = (array) $config->get('sentry.ignore_commands', []);
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
            AfterExecute::class,
        ];
    }

    /**
     * @param BeforeHandle|AfterExecute $event
     */
    public function process(object $event): void
    {
        if (Str::is($this->ignoreCommands, $event->getCommand()->getName())) {
            return;
        }

        match ($event::class) {
            BeforeHandle::class => $this->startTransaction($event),
            AfterExecute::class => $this->finishTransaction($event),
        };
    }

    protected function startTransaction(BeforeHandle $event): void
    {
        $sentry = SentrySdk::init();
        $command = $event->getCommand();
        $context = new TransactionContext();
        $context->setName($command->getName() ?: '<unnamed command>');
        $context->setSource(TransactionSource::custom());
        $context->setOp('command.execute');
        $context->setDescription($command->getDescription());
        $context->setStartTimestamp(microtime(true));

        $data = [
            'command.arguments' => (fn () => $this->input->getArguments())->call($command),
            'command.options' => (fn () => $this->input->getOptions())->call($command),
        ];
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

    protected function finishTransaction(AfterExecute $event): void
    {
        $transaction = TraceContext::getTransaction();

        if (! $transaction) {
            return;
        }

        $command = $event->getCommand();
        $exitCode = (fn () => $this->exitCode ?? SymfonyCommand::SUCCESS)->call($command);
        $data = [];
        $tags = [
            'command.exit_code' => $exitCode,
        ];

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            $data['command.exception.stack_trace'] = (string) $exception;
        }

        $transaction->setData($data);
        $transaction->setTags($tags);
        $transaction->setStatus($exitCode == SymfonyCommand::SUCCESS ? SpanStatus::ok() : SpanStatus::internalError());
        $transaction->finish(microtime(true));
    }
}
