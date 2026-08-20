<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use RuntimeException;
use Sentry\ClientInterface;
use Sentry\Options;
use Sentry\State\HubInterface;
use Sentry\State\RuntimeContextManager;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;

// Load the component's class_map replacement for the SDK class (the same file the
// ConfigProvider registers via the container class_map), so these tests exercise the
// coroutine-safe implementation instead of the raw SDK class.
require_once __DIR__ . '/../../src/sentry/class_map/RuntimeContextManager.php';

beforeEach(function () {
    // Mock hub + client so the RuntimeContextManager constructor never resolves the container
    // ($baseHub->getClient() returns a truthy mock client).
    $this->client = $this->createMock(ClientInterface::class);
    $this->client->method('getOptions')->willReturn(new Options());
    $this->client->method('captureEvent')->willReturn(null);

    $this->baseHub = $this->createMock(HubInterface::class);
    $this->baseHub->method('getClient')->willReturn($this->client);
});

test('startContext creates an isolated hub and marks the context active', function () {
    // Tests run inside a coroutine via FriendsOfHyperf\Tests\TestCase, so the
    // CoArrayObject-backed manager has a clean per-coroutine context here.
    $manager = new RuntimeContextManager($this->baseHub);
    $manager->startContext();

    expect($manager->hasActiveContext())->toBeTrue();
    expect($manager->getCurrentContext()->getHub())->not->toBe($this->baseHub);
});

test('endContext forwards the flush timeout to the client', function () {
    $received = [];
    $this->client->method('flush')->willReturnCallback(static function (?int $timeout) use (&$received) {
        $received[] = $timeout;

        return new Result(ResultStatus::success());
    });

    $manager = new RuntimeContextManager($this->baseHub);

    $manager->startContext();
    $manager->endContext(1500);
    expect($received)->toBe([1500]);

    $manager->startContext();
    $manager->endContext(null);
    expect($received)->toBe([1500, 0]);
});

test('endContext does not throw when the client flush throws and still releases the context', function () {
    $this->client->method('flush')->willThrowException(new RuntimeException('transport unavailable'));

    $manager = new RuntimeContextManager($this->baseHub);
    $manager->startContext();

    $manager->endContext();

    expect($manager->hasActiveContext())->toBeFalse();
});

test('startContext is idempotent for the current execution key', function () {
    $manager = new RuntimeContextManager($this->baseHub);

    $manager->startContext();
    $firstId = $manager->getCurrentContext()->getId();

    $manager->startContext();
    $secondId = $manager->getCurrentContext()->getId();

    expect($secondId)->toBe($firstId);
});
