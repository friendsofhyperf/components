<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Stringable\Str;

class TelescopeConfig
{
    public function __construct(private ConfigInterface $config)
    {
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->config->get('telescope.' . $key, $default);
    }

    /**
     * @return array{enable: bool, host: string, port: int}
     */
    public function getServerOptions(): array
    {
        return array_replace([
            'enable' => false,
            'host' => '0.0.0.0',
            'port' => 9509,
        ], (array) $this->get('server', []));
    }

    public function isServerEnable(): bool
    {
        return (bool) $this->getServerOptions()['enable'];
    }

    public function getServerHost(): string
    {
        return (string) ($this->getServerOptions()['host'] ?: '0.0.0.0');
    }

    public function getServerPort(): int
    {
        return (int) ($this->getServerOptions()['port'] ?: 9509);
    }

    /**
     * @return array{connection: string, query_slow: int}
     */
    public function getDatabaseOptions(): array
    {
        return array_replace([
            'connection' => 'default',
            'query_slow' => 50,
        ], (array) $this->get('database', []));
    }

    public function getDatabaseConnection(): string
    {
        return (string) ($this->getDatabaseOptions()['connection'] ?: 'default');
    }

    public function getDatabaseQuerySlow(): int
    {
        return (int) $this->getDatabaseOptions()['query_slow'];
    }

    public function isEnable(string $key): bool
    {
        return (bool) $this->get('enable.' . $key, false);
    }

    public function getTimezone(): string
    {
        return (string) $this->get('timezone', 'Asia/Shanghai');
    }

    public function getSaveMode(): int
    {
        return match ($this->get('save_mode', Telescope::SYNC)) {
            Telescope::ASYNC => Telescope::ASYNC,
            Telescope::SYNC => Telescope::SYNC,
            default => Telescope::SYNC,
        };
    }

    public function getPath(): string
    {
        return (string) $this->get('path', 'telescope');
    }

    /**
     * @return string[]
     */
    public function getIgnoreLogs(): array
    {
        return (array) $this->get('ignore_logs', []);
    }

    public function isLogIgnored(string $name): bool
    {
        if (empty($this->getIgnoreLogs())) {
            return false;
        }

        return in_array($name, $this->getIgnoreLogs(), true);
    }

    /**
     * @return string[]
     */
    public function getOnlyPaths(): array
    {
        return (array) $this->get('only_paths', []);
    }

    public function isPatchOnly(string $path): bool
    {
        if (empty($this->getOnlyPaths())) {
            return false;
        }

        return Str::is($this->getOnlyPaths(), $path);
    }

    /**
     * @return string[]
     */
    public function getIgnorePaths(): array
    {
        $defaultIgnorePaths = [
            'nova-api*',
            '*favicon.ico',
            'telescope-api*',
            'vendor/telescope*',
        ];
        $ignorePaths = (array) $this->get('ignore_paths', []);

        return array_merge($defaultIgnorePaths, $ignorePaths);
    }

    public function isPathIgnored(string $path): bool
    {
        return Str::is($this->getIgnorePaths(), $path);
    }
}
