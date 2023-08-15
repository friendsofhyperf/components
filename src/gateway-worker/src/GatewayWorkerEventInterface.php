<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\GatewayWorker;

use GatewayWorker\BusinessWorker;

interface GatewayWorkerEventInterface
{
    public static function onWorkerStart(BusinessWorker $businessWorker): void;

    public static function onConnect(string $clientId): void;

    public static function onWebSocketConnect(string $clientId, array $data): void;

    public static function onMessage(string $clientId, mixed $revData): void;

    public static function onClose(string $clientId): void;
}
