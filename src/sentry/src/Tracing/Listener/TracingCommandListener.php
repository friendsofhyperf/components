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
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
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
    protected array $ignoreCommands = [];

    public function __construct(
        ConfigInterface $config,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
        $this->ignoreCommands = $config->get('sentry.ignore_commands', []);
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
        if (in_array($event->getCommand()->getName(), $this->ignoreCommands)) {
            return;
        }

        $sentry = SentrySdk::getCurrentHub();

        match ($event::class) {
            BeforeHandle::class => $this->startTransaction($sentry, $event),
            AfterExecute::class => $this->finishTransaction($sentry, $event),
        };
    }

    protected function startTransaction(HubInterface $sentry, BeforeHandle $event): void
    {
        $command = $event->getCommand();
        $context = new TransactionContext();
        $context->setName($command->getName() ?: '<unnamed command>');
        $context->setSource(TransactionSource::custom());
        $context->setOp('command.execute');
        $context->setDescription($command->getDescription());
        $context->setStartTimestamp(microtime(true));

        $data = [];
        if ($this->tagManager->has('command.arguments')) {
            $data[$this->tagManager->get('command.arguments')] = (fn () => $this->input->getArguments())->call($command);
        }
        if ($this->tagManager->has('command.options')) {
            $data[$this->tagManager->get('command.options')] = (fn () => $this->input->getOptions())->call($command);
        }
        $context->setData($data);
        $transaction = $sentry->startTransaction($context);
        TraceContext::setTransaction($transaction);
        $sentry->setSpan($transaction);
        TraceContext::setSpan($transaction);
    }

    protected function finishTransaction(HubInterface $sentry, AfterExecute $event): void
    {
        $transaction = TraceContext::getTransaction();

        if (! $transaction) {
            return;
        }

        $command = $event->getCommand();
        $tags = [];
        if ($this->tagManager->has('command.exit_code')) {
            $tags[$this->tagManager->get('command.exit_code')] = (fn () => $this->exitCode ?? SymfonyCommand::SUCCESS)->call($command);
        }
        $transaction->setTags($tags);
        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError());
            $transaction->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('command.exception.stack_trace')) {
                $transaction->setData([
                    $this->tagManager->get('command.exception.stack_trace') => (string) $exception,
                ]);
            }
        }
        $transaction->finish(microtime(true));
    }
}
