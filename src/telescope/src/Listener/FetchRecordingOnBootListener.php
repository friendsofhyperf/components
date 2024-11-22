<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\PipeMessage;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Process\ProcessCollector;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Swoole\Process;
use Swoole\Server as SwooleServer;

class FetchRecordingOnBootListener implements ListenerInterface
{
    private Timer $timer;

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected TelescopeConfig $telescopeConfig,
        protected StdoutLoggerInterface $logger
    ) {
        $this->timer = new Timer($logger);
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * @param BootApplication|MainWorkerStart|MainCoroutineServerStart|object $event
     */
    public function process(object $event): void
    {
        if ($event instanceof BootApplication) {
            Coroutine::create(function () {
                $this->config->set('telescope.recording', (bool) $this->telescopeConfig->fetchRecording());
            });
            return;
        }

        $callback = match (true) {
            $event instanceof MainWorkerStart => fn ($pipeMessage) => $this->shareMessageToWorkers($pipeMessage),
            $event instanceof MainCoroutineServerStart => fn ($pipeMessage) => $this->config->set('telescope.recording', (bool) $pipeMessage->recording),
            default => fn () => null,
        };

        $this->timer->tick(1, function () use ($callback) {
            $recording = (bool) $this->telescopeConfig->fetchRecording();
            $callback(new PipeMessage($recording));
        });
    }

    private function shareMessageToWorkers(PipeMessage $message): void
    {
        $swooleServer = $this->container->get(SwooleServer::class);
        $workerCount = $swooleServer->setting['worker_num'] - 1;

        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            $swooleServer->sendMessage($message, $workerId);
            $this->logger->debug(sprintf('[Telescope] Let Worker.%s try to update telescope.recording as %s.', $workerId, $message->recording ? 'true' : 'false'));
        }

        /** @var Process[] $processes */
        $processes = ProcessCollector::all();

        if ($processes) {
            $string = serialize($message);
            foreach ($processes as $process) {
                $result = $process->exportSocket()->send($string, 10);
                if ($result === false) {
                    $this->logger->error('Configuration synchronization failed. Please restart the server.');
                }
            }
        }
    }
}
