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

use Closure;
use Hyperf\Collection\Arr;

use function Hyperf\Config\config;

class Telescope
{
    public const SYNC = 0;

    public const ASYNC = 1;

    /**
     * The list of hidden request headers.
     */
    public static array $hiddenRequestHeaders = [
        'authorization',
        'php-auth-pw',
    ];

    /**
     * The list of hidden request parameters.
     */
    public static array $hiddenRequestParameters = [
        'password',
        'password_confirmation',
    ];

    /**
     * The list of hidden response parameters.
     */
    public static array $hiddenResponseParameters = [];

    /**
     * Indicates if Telescope should ignore events fired by Laravel.
     */
    public static bool $ignoreFrameworkEvents = true;

    /**
     * Indicates if Telescope should use the dark theme.
     */
    public static bool $useDarkTheme = false;

    /**
     * Indicates if Telescope should record entries.
     */
    public static bool $shouldRecord = false;

    /**
     * Indicates if Telescope migrations will be run.
     */
    public static bool $runsMigrations = true;

    /**
     * The callbacks that add tags to the record.
     */
    public static array $tagUsing = [];

    /**
     * The callbacks that filter the entries that should be recorded.
     */
    public static array $filterUsing = [];

    public static function recordCache(IncomingEntry $entry): void
    {
        static::record(EntryType::CACHE, $entry);
    }

    public static function recordCommand(IncomingEntry $entry): void
    {
        static::record(EntryType::COMMAND, $entry);
    }

    public static function recordEvent(IncomingEntry $entry): void
    {
        static::record(EntryType::EVENT, $entry);
    }

    public static function recordException(IncomingEntry $entry): void
    {
        static::record(EntryType::EXCEPTION, $entry);
    }

    public static function recordLog(IncomingEntry $entry): void
    {
        static::record(EntryType::LOG, $entry);
    }

    public static function recordQuery(IncomingEntry $entry): void
    {
        static::record(EntryType::QUERY, $entry);
    }

    public static function recordRedis(IncomingEntry $entry): void
    {
        static::record(EntryType::REDIS, $entry);
    }

    public static function recordRequest(IncomingEntry $entry): void
    {
        static::record(EntryType::REQUEST, $entry);
    }

    public static function recordService(IncomingEntry $entry): void
    {
        static::record(EntryType::SERVICE, $entry);
    }

    public static function recordClientRequest(IncomingEntry $entry): void
    {
        static::record(EntryType::CLIENT_REQUEST, $entry);
    }

    /**
     * @deprecated the method has been deprecated and its usage is discouraged
     */
    public static function getAppName(): string
    {
        return config('app_name', '') ? '[' . config('app_name', '') . '] ' : '';
    }

    public static function getQuerySlow(): int
    {
        return config('telescope.database.query_slow', 50);
    }

    /**
     * Add a callback that adds tags to the record.
     *
     * @return static
     */
    public static function tag(Closure $callback)
    {
        static::$tagUsing[] = $callback;

        return new static();
    }

    /**
     * Set the callback that filters the entries that should be recorded.
     *
     * @return static
     */
    public static function filter(Closure $callback)
    {
        static::$filterUsing[] = $callback;

        return new static();
    }

    /**
     * Determine if the given entry should be recorded.
     */
    protected static function shouldRecord(IncomingEntry $entry): bool
    {
        foreach (static::$filterUsing as $callback) {
            if (! $callback($entry)) {
                return false;
            }
        }
        return true;
    }

    protected static function record(string $type, IncomingEntry $entry): void
    {
        $batchId = (string) TelescopeContext::getBatchId();
        $subBatchId = (string) TelescopeContext::getSubBatchId();
        $entry->batchId($batchId)->subBatchId($subBatchId)->type($type)->user();

        if (! static::shouldRecord($entry)) {
            return;
        }

        $entry->tags(Arr::collapse(array_map(function ($tagCallback) use ($entry) {
            return $tagCallback($entry);
        }, static::$tagUsing)));

        match (config('telescope.save_mode', 0)) {
            self::ASYNC => TelescopeContext::addEntry($entry),
            self::SYNC => $entry->create(),
            default => $entry->create(),
        };
    }
}
