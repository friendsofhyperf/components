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

use Faker\Container\ContainerInterface;
use FriendsOfHyperf\Telescope\PipeMessage;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Redis\Redis;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Swoole\Server as SwooleServer;

class FetchRecordingOnBootListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected Redis $redis,
        protected TelescopeConfig $telescopeConfig,
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $callback = match (true) {
            $event instanceof MainWorkerStart => fn ($pipeMessage) => $this->shareMessageToWorkers($pipeMessage),
            $event instanceof MainCoroutineServerStart => fn ($pipeMessage) => $this->config->set('telescope.recording', (bool) $pipeMessage->recording),
            default => fn () => null,
        };
        $key = $this->telescopeConfig->getRecordingCacheKey();

        $timer = new Timer($this->logger);
        $timer->tick(1000, function () use ($callback, $key) {
            $recording = (bool) $this->redis->get($key);
            $pipeMessage = new PipeMessage($recording);
            $callback($pipeMessage);
        });
    }

    private function getSwooleServer(): SwooleServer
    {
        return $this->container->get(SwooleServer::class);
    }

    private function shareMessageToWorkers(PipeMessage $message)
    {
        $swooleServer = $this->getSwooleServer();
        $workerCount = $swooleServer->setting['worker_num'] - 1;

        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            $swooleServer->sendMessage($message, $workerId);
            $this->logger->debug(sprintf('[Telescope] Let Worker.%s try to update telescope.recording as %s.', $workerId, $message->recording ? 'true' : 'false'));
        }
    }
}
