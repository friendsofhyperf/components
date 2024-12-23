<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

use Hyperf\Collection\Arr;

use function Hyperf\Support\env;

class Config
{
    public function __construct(private array $configs = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->configs, $key, $default);
    }

    public function enable(): bool
    {
        return $this->get('enable', false);
    }

    public function host(): string
    {
        return $this->get('host', '');
    }

    public function port(): int
    {
        return $this->get('port', 3306);
    }

    public function user(): string
    {
        return $this->get('user', '');
    }

    public function password(): string
    {
        return $this->get('password', '');
    }

    public function databasesOnly(): array
    {
        return $this->get('databases_only', []);
    }

    public function tablesOnly(): array
    {
        return $this->get('tables_only', []);
    }

    public function heartbeatPeriod(): int
    {
        return $this->get('heartbeat_period', 3);
    }

    public function connectRetries(): int
    {
        return $this->get('connect_retries', 10);
    }

    /**
     * @return class-string<AbstractSubscriber>[]
     */
    public function subscribers(): array
    {
        return $this->get('subscribers', []);
    }

    /**
     * @return array{enable: bool, prefix: string, expires: int, keepalive_interval: int, retry_interval: int}
     */
    public function serverMutex(): array
    {
        return array_replace([
            'enable' => true,
            'prefix' => env('APP_ENV', 'dev') . '_',
            'expires' => 30,
            'keepalive_interval' => 10,
            'retry_interval' => 10,
        ], $this->get('server_mutex', []));
    }

    /**
     * @return array{enable: bool, interval: int}
     */
    public function healthMonitor(): array
    {
        return array_replace([
            'enable' => true,
            'interval' => 30,
        ], $this->get('health_monitor', []));
    }

    /**
     * @return array{version: string, expires: int, interval: int}
     */
    public function snapshot(): array
    {
        return array_replace([
            'version' => '1.0',
            'expires' => 24 * 3600,
            'interval' => 10,
        ], $this->get('snapshot', []));
    }

    /**
     * @return array{limit: int}
     */
    public function concurrent(): array
    {
        return array_replace([
            'limit' => 1,
        ], $this->get('concurrent', []));
    }
}
