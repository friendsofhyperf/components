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

use Hyperf\Context\Context;
use Hyperf\Contract\PackerInterface;
use Hyperf\Stringable\Str;

use function Hyperf\Coroutine\defer;

class TelescopeContext
{
    public const BATCH_ID = 'telescope.context.batch_id';

    public const SUB_BATCH_ID = 'telescope.context.sub_batch_id';

    public const ENTRIES = 'telescope.context.entries';

    public const CACHE_PACKER = 'telescope.context.cache_packer';

    public const CACHE_DRIVER = 'telescope.context.cache_driver';

    public const MIDDLEWARES = 'telescope.context.middlewares';

    public const GRPC_REQUEST_PAYLOAD = 'telescope.context.grpc.request.payload';

    public const GRPC_RESPONSE_PAYLOAD = 'telescope.context.grpc.response.payload';

    public static function setBatchId(string $batchId): void
    {
        Context::set(self::BATCH_ID, $batchId);
    }

    public static function getBatchId(): ?string
    {
        return Context::get(self::BATCH_ID) ?: null;
    }

    public static function setSubBatchId(string $batchId): void
    {
        Context::set(self::SUB_BATCH_ID, $batchId);
    }

    public static function getSubBatchId(): ?string
    {
        return Context::get(self::SUB_BATCH_ID) ?: null;
    }

    /**
     * @deprecated since v3.1, the method has been deprecated and its usage is discouraged, will be removed in v3.2
     */
    public static function setCachePacker(PackerInterface $packer): void
    {
        Context::set(self::CACHE_PACKER, $packer);
    }

    /**
     * @deprecated since v3.1, the method has been deprecated and its usage is discouraged, will be removed in v3.2
     */
    public static function getCachePacker(): ?PackerInterface
    {
        /** @var PackerInterface|null $packer */
        $packer = Context::get(self::CACHE_PACKER);
        return $packer instanceof PackerInterface ? $packer : null;
    }

    public static function getMiddlewares(): ?array
    {
        return Context::get(self::MIDDLEWARES) ?: null;
    }

    public static function setMiddlewares(array $middlewares): void
    {
        Context::set(self::MIDDLEWARES, $middlewares);
    }

    public static function getCacheDriver(): ?string
    {
        return Context::get(self::CACHE_DRIVER) ?: null;
    }

    public static function setCacheDriver(string $driver): void
    {
        Context::set(self::CACHE_DRIVER, $driver);
    }

    public static function addEntry(IncomingEntry $entry): void
    {
        if (! Context::has(self::ENTRIES)) {
            Context::set(self::ENTRIES, []);

            defer(function () {
                /** @var IncomingEntry[] $entries */
                $entries = Context::get(self::ENTRIES);
                foreach ($entries as $entry) {
                    $entry->create();
                }
                Context::destroy(self::ENTRIES);
            });
        }

        /** @var IncomingEntry[] $entries */
        $entries = Context::get(self::ENTRIES);
        $entries[] = $entry;

        Context::set(self::ENTRIES, $entries);
    }

    public static function setGrpcRequestPayload(array $payload): void
    {
        Context::set(self::GRPC_REQUEST_PAYLOAD, $payload);
    }

    public static function getGrpcRequestPayload(): ?array
    {
        return Context::get(self::GRPC_REQUEST_PAYLOAD) ?: null;
    }

    public static function setGrpcResponsePayload(array $payload): void
    {
        Context::set(self::GRPC_RESPONSE_PAYLOAD, $payload);
    }

    public static function getGrpcResponsePayload(): ?array
    {
        return Context::get(self::GRPC_RESPONSE_PAYLOAD) ?: null;
    }

    public static function getOrSetBatch(?string $batchId = null): void
    {
        if ($batchId) {
            $subBatchId = Str::orderedUuid()->toString();
            self::setSubBatchId($subBatchId);
        } else {
            $batchId = Str::orderedUuid()->toString();
        }

        self::setBatchId($batchId);
    }
}
