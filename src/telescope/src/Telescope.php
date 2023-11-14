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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;

class Telescope
{
    /**
     * The callbacks that filter the entries that should be recorded.
     */
    public static array $filterUsing = [];

    /**
     * The callbacks that filter the batches that should be recorded.
     */
    public static array $filterBatchUsing = [];

    /**
     * The callback executed after queuing a new entry.
     *
     * @var Closure
     */
    public static $afterRecordingHook;

    /**
     * The callbacks executed after storing the entries.
     *
     * @var Closure[]
     */
    public static array $afterStoringHooks = [];

    /**
     * The callbacks that add tags to the record.
     *
     * @var Closure[]
     */
    public static array $tagUsing = [];

    /**
     * The list of queued entries to be stored.
     */
    public static array $entriesQueue = [];

    /**
     * The list of queued entry updates.
     */
    public static array $updatesQueue = [];

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

    public static function getAppName(): string
    {
        $container = ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);
        return $config->get('telescope.app.name', '') ? '[' . $config->get('telescope.app.name', '') . '] ' : '';
    }

    public static function getQuerySlow(): int
    {
        $container = ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);
        return $config->get('telescope.database.query_slow', 50);
    }

    protected static function record(string $type, IncomingEntry $entry): void
    {
        $batchId = (string) TelescopeContext::getBatchId();
        $subBatchId = (string) TelescopeContext::getSubBatchId();
        $entry->batchId($batchId)->subBatchId($subBatchId)->type($type)->user();
        // $entry->create();
        TelescopeContext::addEntry($entry);
    }
}
