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
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * @property int $exitCode
 * @property \Symfony\Component\Console\Input\InputInterface $input
 */
class TracingCommandListener implements ListenerInterface
{
    use SpanStarter;

    /**
     * @var string[]
     */
    protected array $ignoreCommands = [];

    public function __construct(
        ConfigInterface $config,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
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
     * @param BeforeHandle|AfterExecute|object $event
     */
    public function process(object $event): void
    {
        if (
            ! $this->switcher->isTracingEnable('command')
            || Str::is($this->ignoreCommands, $event->getCommand()->getName())
        ) {
            return;
        }

        match ($event::class) {
            BeforeHandle::class => $this->startTransaction($event),
            AfterExecute::class => $this->finishTransaction($event),
            default => null,
        };
    }

    protected function startTransaction(BeforeHandle $event): void
    {
        $command = $event->getCommand();

        $this->continueTrace(
            name: $command->getName() ?: '<unnamed command>',
            op: 'command.execute',
            description: $command->getDescription(),
            source: TransactionSource::custom()
        );
    }

    protected function finishTransaction(AfterExecute $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        // If this transaction is not sampled, we can stop here to prevent doing work for nothing
        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }
        $command = $event->getCommand();

        $exitCode = (fn () => $this->exitCode ?? SymfonyCommand::SUCCESS)->call($command);
        $data = [];
        $tags = [];

        if ($this->tagManager->has('command.arguments')) {
            $data[$this->tagManager->get('command.arguments')] = (fn () => $this->input->getArguments())->call($command);
        }
        if ($this->tagManager->has('command.options')) {
            $data[$this->tagManager->get('command.options')] = (fn () => $this->input->getOptions())->call($command);
        }
        if ($this->tagManager->has('command.exit_code')) {
            $tags[$this->tagManager->get('command.exit_code')] = $exitCode;
        }

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $tags = array_merge($tags, [
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('command.exception.stack_trace')) {
                $data[$this->tagManager->get('command.exception.stack_trace')] = (string) $exception;
            }
        }

        $transaction->setStatus($exitCode == SymfonyCommand::SUCCESS ? SpanStatus::ok() : SpanStatus::internalError());
        $transaction->setData($data);
        $transaction->setTags($tags);

        SentrySdk::getCurrentHub()->setSpan($transaction);
        $transaction->finish(microtime(true));
    }
}
