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
use Hyperf\Redis\Redis;
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

    public function getStorageDriver(string $default = 'database'): string
    {
        return (string) $this->get('driver', $default);
    }

    public function getStorageOptions(string $driver = 'database'): array
    {
        return (array) $this->get('storage.' . $driver, []);
    }

    public function getQuerySlow(): int
    {
        return (int) $this->get('query_slow', 50);
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
