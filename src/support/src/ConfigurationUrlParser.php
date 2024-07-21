<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support;

use Hyperf\Collection\Arr;
use InvalidArgumentException;
use JsonException;

class ConfigurationUrlParser
{
    /**
     * The drivers aliases map.
     */
    protected static array $driverAliases = [
        'mssql' => 'sqlsrv',
        'mysql2' => 'mysql', // RDS
        'postgres' => 'pgsql',
        'postgresql' => 'pgsql',
        'sqlite3' => 'sqlite',
        'redis' => 'tcp',
        'rediss' => 'tls',
    ];

    /**
     * Parse the database configuration, hydrating options using a database configuration URL if possible.
     */
    public function parseConfiguration(array|string $config): array
    {
        if (is_string($config)) {
            $config = ['url' => $config];
        }

        $url = Arr::pull($config, 'url');

        if (! $url) {
            return $config;
        }

        $rawComponents = $this->parseUrl($url);

        $decodedComponents = $this->parseStringsToNativeTypes(
            array_map('rawurldecode', $rawComponents)
        );

        return array_merge(
            $config,
            $this->getPrimaryOptions($decodedComponents),
            $this->getQueryOptions($rawComponents)
        );
    }

    /**
     * Get all of the current drivers' aliases.
     */
    public static function getDriverAliases(): array
    {
        return static::$driverAliases;
    }

    /**
     * Add the given driver alias to the driver aliases array.
     */
    public static function addDriverAlias(string $alias, string $driver): void
    {
        static::$driverAliases[$alias] = $driver;
    }

    /**
     * Get the primary database connection options.
     */
    protected function getPrimaryOptions(array $url): array
    {
        return array_filter([
            'driver' => $this->getDriver($url),
            'database' => $this->getDatabase($url),
            'host' => $url['host'] ?? null,
            'port' => $url['port'] ?? null,
            'username' => $url['user'] ?? null,
            'password' => $url['pass'] ?? null,
        ], fn ($value) => ! is_null($value));
    }

    /**
     * Get the database driver from the URL.
     */
    protected function getDriver(array $url): ?string
    {
        $alias = $url['scheme'] ?? null;

        if (! $alias) {
            return null;
        }

        return static::$driverAliases[$alias] ?? $alias;
    }

    /**
     * Get the database name from the URL.
     */
    protected function getDatabase(array $url): ?string
    {
        $path = $url['path'] ?? null;

        return $path && $path !== '/' ? substr($path, 1) : null;
    }

    /**
     * Get all of the additional database options from the query string.
     */
    protected function getQueryOptions(array $url): array
    {
        $queryString = $url['query'] ?? null;

        if (! $queryString) {
            return [];
        }

        $query = [];

        parse_str($queryString, $query);

        return $this->parseStringsToNativeTypes($query);
    }

    /**
     * Parse the string URL to an array of components.
     */
    protected function parseUrl(string $url): array
    {
        $url = preg_replace('#^(sqlite3?):///#', '$1://null/', $url);

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false) {
            throw new InvalidArgumentException('The database configuration URL is malformed.');
        }

        return $parsedUrl;
    }

    /**
     * Convert string casted values to their native types.
     */
    protected function parseStringsToNativeTypes(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'parseStringsToNativeTypes'], $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
        }

        return $value;
    }
}
