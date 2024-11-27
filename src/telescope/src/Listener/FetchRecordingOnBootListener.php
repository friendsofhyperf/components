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
use FriendsOfHyperf\Telescope\BroadcastRecordingPipeMessage;
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
use Swoole\Process;
use Throwable;

class FetchRecordingOnBootListener implements ListenerInterface
{
    private Timer $timer;

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
        $this->config->set('telescope.recording', $this->telescopeConfig->fetchRecording());

        $this->timer->tick(1, function () {
            try {
                $recording = $this->telescopeConfig->fetchRecording();
                $this->broadcaster->broadcast(new BroadcastRecordingPipeMessage($recording));
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage());
            }
        });
    }
}
