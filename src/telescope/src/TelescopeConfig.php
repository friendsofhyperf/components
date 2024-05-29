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
use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;
use Hyperf\Stringable\Str;
use Psr\Http\Message\ServerRequestInterface;

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

    public function isServerEnable(): bool
    {
        return (bool) $this->getServerOptions()['enable'];
    }

    /**
     * @deprecated since v3.1, will remove in v3.2
     */
    public function getServerHost(): string
    {
        return (string) ($this->getServerOptions()['host'] ?: '0.0.0.0');
    }

    /**
     * @deprecated since v3.1, will remove in v3.2
     */
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

    public function getAppName(): string
    {
        return (string) $this->config->get('app_name', '');
    }

    public function getTimezone(): string
    {
        return (string) $this->get('timezone', date_default_timezone_get());
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
            $this->getPath() . '*',
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
}
