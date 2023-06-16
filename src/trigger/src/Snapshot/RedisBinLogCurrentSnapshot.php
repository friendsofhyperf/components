<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Trigger\Snapshot;

use FriendsOfHyperf\Trigger\Consumer;
use Hyperf\Redis\Redis;
use MySQLReplication\BinLog\BinLogCurrent;

use function Hyperf\Support\with;

class RedisBinLogCurrentSnapshot implements BinLogCurrentSnapshotInterface
{
    public function __construct(
        private Consumer $consumer,
        private Redis $redis
    ) {
    }

    public function set(BinLogCurrent $binLogCurrent): void
    {
        $this->redis->set($this->key(), serialize($binLogCurrent));
        $this->redis->expire($this->key(), (int) $this->consumer->getOption('snapshot.expires', 24 * 3600));
    }

    public function get(): ?BinLogCurrent
    {
        return with($this->redis->get($this->key()), function ($data) {
            $data = unserialize((string) $data);

            if (! $data instanceof BinLogCurrent) {
                return null;
            }

            return $data;
        });
    }

    private function key(): string
    {
        return join(':', [
            'trigger',
            'snapshot',
            'binLogCurrent',
            $this->consumer->getOption('snapshot.version', '1.0'),
            $this->consumer->getConnection(),
        ]);
    }
}
