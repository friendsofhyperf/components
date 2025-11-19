<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Snapshot;

use FriendsOfHyperf\Trigger\Consumer;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Redis\Redis;
use MySQLReplication\BinLog\BinLogCurrent;
use RuntimeException;
use Throwable;

use function Hyperf\Support\with;

class RedisBinLogCurrentSnapshot implements BinLogCurrentSnapshotInterface
{
    public function __construct(
        private Consumer $consumer,
        private Redis $redis,
        private StdoutLoggerInterface $logger,
    ) {
    }

    public function set(BinLogCurrent $binLogCurrent): void
    {
        $this->redis->set($this->key(), serialize($binLogCurrent));
        $this->redis->expire($this->key(), (int) $this->consumer->config->get('snapshot.expires', 24 * 3600));
    }

    public function get(): ?BinLogCurrent
    {
        return with($this->redis->get($this->key()), function ($data) {
            try {
                $data = unserialize((string) $data);

                if (! $data instanceof BinLogCurrent) {
                    throw new RuntimeException('Invalid BinLogCurrent cache.');
                }

                return $data;
            } catch (Throwable $e) {
                try {
                    $this->redis->rename(
                        $this->key(),
                        $key = $this->key() . '.bak_' . date('YmdHis')
                    );
                    $this->logger->warning('BinLogCurrent cache invalid, rename to ' . $key);
                } catch (Throwable) {
                    $this->logger->warning('BinLogCurrent cache invalid, and rename failed.');
                }

                return null;
            }
        });
    }

    private function key(): string
    {
        return join(':', [
            'trigger',
            'snapshot',
            'binLogCurrent',
            $this->consumer->config->get('snapshot.version', '1.0'),
            $this->consumer->connection,
        ]);
    }
}
