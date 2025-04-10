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

use FriendsOfHyperf\IpcBroadcaster\Contract\BroadcasterInterface;
use FriendsOfHyperf\Telescope\PipeMessage;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Process\Event\BeforeCoroutineHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Throwable;

class FetchRecordingOnBootListener implements ListenerInterface
{
    private Timer $timer;

    private bool $lastRecordingStatus;

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected TelescopeConfig $telescopeConfig,
        protected BroadcasterInterface $broadcaster,
        protected StdoutLoggerInterface $logger
    ) {
        $this->timer = new Timer($logger);
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
            // Process Style
            BeforeProcessHandle::class,
            MainWorkerStart::class,
            // Coroutine Style
            BeforeCoroutineHandle::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * @param BootApplication|MainWorkerStart|MainCoroutineServerStart|object $event
     */
    public function process(object $event): void
    {
        $initialRecording = $this->telescopeConfig->fetchRecording();
        $this->lastRecordingStatus = $initialRecording;

        if ($event instanceof BeforeHandle) {
            $this->config->set('telescope.recording', $initialRecording);
            return;
        }
        $this->config->set('telescope.recording', $initialRecording);

        $this->timer->tick(1, function () {
            try {
                $recording = $this->telescopeConfig->fetchRecording();
                if ($this->lastRecordingStatus !== $recording) {
                    $this->lastRecordingStatus = $recording;
                    $this->broadcaster->broadcast(new PipeMessage($recording));
                }
            } catch (Throwable $e) {
                $this->logger->error(sprintf(
                    '[Telescope] Failed to fetch recording: %s in %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
            }
        });
    }
}
