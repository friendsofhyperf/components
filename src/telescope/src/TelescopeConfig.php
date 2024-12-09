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

use FriendsOfHyperf\Telescope\Server\Server;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;
use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ServerRequestInterface;

class TelescopeConfig
{
    public function __construct(
        private ConfigInterface $config,
        private Redis $redis
    ) {
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->config->get('telescope.' . $key, $default);
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2
     * @return array{enable: bool, host: string, port: int}
     */
    public function getServerOptions(): array
    {
        return array_replace([
            'enable' => false,
            'name' => 'telescope',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9509,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Server::class, 'onRequest'],
            ],
        ], (array) $this->get('server', []));
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2
     */
    public function isServerEnable(): bool
    {
        return (bool) $this->getServerOptions()['enable'];
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2
     */
    public function getServerHost(): string
    {
        return (string) ($this->getServerOptions()['host'] ?: '0.0.0.0');
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2
     */
    public function getServerPort(): int
    {
        return (int) ($this->getServerOptions()['port'] ?: 9509);
    }

    /**
     * @return array{connection:string, query_slow:int, chunk:int}
     */
    public function getDatabaseOptions(): array
    {
        $databaseOptions = $this->get('storage.database')
            ?? $this->get('database') // will be removed in v3.2
            ?? [];
        return array_replace([
            'connection' => 'default',
            'query_slow' => 50,
            'chunk' => 1000,
        ], (array) $databaseOptions);
    }

    public function getDatabaseConnection(): string
    {
        return (string) $this->getDatabaseOptions()['connection'];
    }

    public function getDatabaseChunk(): int
    {
        return (int) $this->getDatabaseOptions()['chunk'];
    }

    public function getDatabaseQuerySlow(): int
    {
        return (int) $this->getDatabaseOptions()['query_slow'];
    }

    public function isEnable(string $key): bool
    {
        return $this->isEnabled()
            && ((bool) $this->get('enable.' . $key, false))
            && $this->isRecording();
    }

    public function isEnabled(): bool
    {
        return (bool) $this->get('enabled', true); // will default to false after v3.2
    }

    public function getAppName(): string
    {
        return (string) $this->config->get('app_name', '');
    }

    public function getTimezone(): string
    {
        return (string) $this->get('timezone', date_default_timezone_get());
    }

    public function getRecordMode(): RecordMode
    {
        /** @var RecordMode|int $mode */
        $mode = $this->get('record_mode', RecordMode::SYNC);

        if ($mode instanceof RecordMode) {
            return $mode;
        }

        return RecordMode::tryFrom((int) $mode) ?: RecordMode::SYNC;
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

    public function isPatchOnly(ServerRequestInterface|string $path): bool
    {
        if ($path instanceof ServerRequestInterface) {
            $path = $path->getUri()->getPath();
        }

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
            trim($this->getPath(), '/') . '*',
        ];
        $ignorePaths = (array) $this->get('ignore_paths', []);

        return array_merge($defaultIgnorePaths, $ignorePaths);
    }

    public function isPathIgnored(ServerRequestInterface|string $path): bool
    {
        if ($path instanceof ServerRequestInterface) {
            $path = $path->getUri()->getPath();
        }

        return Str::is($this->getIgnorePaths(), rawurldecode(trim($path, '/')));
    }

    public function getIgnoreCommands(): array
    {
        return $this->get('ignore_commands', []);
    }

    public function setRecording(bool $recording = true): void
    {
        $this->redis->set($this->getRecordingKey(), (int) $recording);
        $this->config->set('telescope.recording', $recording);
    }

    public function fetchRecording(): bool
    {
        /** @var string|false $recording */
        $recording = $this->redis->get($this->getRecordingKey());
        // default record when key does not exist
        if ($recording === false) {
            return true;
        }
        return (bool) $recording;
    }

    public function isRecording(): bool
    {
        return (bool) $this->config->get('telescope.recording', true);
    }

    private function getRecordingKey(): string
    {
        return sprintf('telescope:%s:recording', $this->getAppName());
    }
}
