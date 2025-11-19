<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Command;

use FriendsOfHyperf\Trigger\Mutex\ServerMutexInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class ServerMutexCommand extends \Hyperf\Command\Command
{
    protected ?string $signature = 'trigger:server-mutex {action : list|release} {--C|connection=default : The connection name}';

    public function __construct(protected ContainerInterface $container)
    {
        return parent::__construct();
    }

    public function handle()
    {
        $action = $this->input->getArgument('action');
        $redis = $this->container->get(Redis::class);
        $config = $this->container->get(ConfigInterface::class);

        if ($action === 'list') {
            $headers = ['Connection', 'Holder', 'Owner', 'Expires At'];
            $mutexes = collect($config->get('trigger.connections', []))
                ->reject(function ($config, $connection) {
                    return ! $config['server_mutex']['enable'];
                })
                ->transform(function ($config, $connection) use ($redis) {
                    $mutex = make(ServerMutexInterface::class, [
                        'name' => 'trigger:mutex:' . $connection,
                        'options' => $config['server_mutex'] ?? [] + ['connection' => $connection],
                    ]);

                    $holder = (fn () => $this->name ?? 'unknown')->call($mutex);
                    $expiresAt = $redis->ttl($holder);
                    $owner = $redis->get($holder) ?? 'none';

                    return [$connection, $holder, $owner, $expiresAt > 0 ? date('Y-m-d H:i:s', time() + $expiresAt) : 'expired'];
                })
                ->toArray();

            $this->table($headers, $mutexes);
            return;
        }
        if ($action === 'release') {
            $connection = $this->input->getOption('connection');
            $options = $config->get("trigger.connections.{$connection}.server_mutex", []);
            $mutex = make(ServerMutexInterface::class, [
                'name' => 'trigger:mutex:' . $connection,
                'options' => $options + ['connection' => $connection],
            ]);
            $mutex->release(true);
            $this->line("Released mutex for connection: {$connection}");
            return;
        }

        $this->error('Invalid action. Use "list" or "release".');
    }
}
