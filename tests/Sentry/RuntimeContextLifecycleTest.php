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

use FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine;
use FriendsOfHyperf\Tests\TestCase;
use Mockery;
use Sentry\ClientInterface;
use Sentry\Options;
use Sentry\State\HubInterface;
use Sentry\State\RuntimeContextManager;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

// The SDK class is replaced via Hyperf's class_map injection at runtime, which
// is not active in the test process, so load the replacement file explicitly
// to exercise the coroutine-aware implementation under test.
require_once __DIR__ . '/../../src/sentry/class_map/RuntimeContextManager.php';

/**
 * @internal
 */
class RuntimeContextLifecycleTest extends TestCase
{
    public function testStartAndEndContextInsideCoroutine(): void
    {
        $manager = $this->createRuntimeContextManager();

        $manager->startContext();

        $this->assertTrue($manager->hasActiveContext());

        $manager->endContext();

        $this->assertFalse($manager->hasActiveContext());
    }

    #[NonCoroutine]
    public function testStartContextIsIgnoredOnMainCoroutine(): void
    {
        $this->assertSame(-1, Coroutine::getCid());

        $manager = $this->createRuntimeContextManager();

        $manager->startContext();

        // The main coroutine uses a process-level context store that is never
        // reaped, so startContext() is a no-op and the global fallback is used.
        $this->assertFalse($manager->hasActiveContext());
        $this->assertSame('global', $manager->getCurrentContext()->getId());
    }

    public function testContextsAreIsolatedAcrossCoroutines(): void
    {
        $manager = $this->createRuntimeContextManager();
        $channelA = new Channel(1);
        $channelB = new Channel(1);
        $result = [];

        Coroutine::create(function () use ($manager, $channelA, &$result): void {
            $manager->startContext();
            $result['a_id'] = $manager->getCurrentContext()->getId();
            $result['a_active'] = $manager->hasActiveContext();
            $channelA->push('started');
            $channelA->pop(); // Wait for the "end A" signal.
            $manager->endContext();
            $result['a_active_after_end'] = $manager->hasActiveContext();
            $channelA->push('done');
        });

        Coroutine::create(function () use ($manager, $channelB, &$result): void {
            $manager->startContext();
            $result['b_id'] = $manager->getCurrentContext()->getId();
            $result['b_active'] = $manager->hasActiveContext();
            $channelB->push('started');
            $channelB->pop(); // Wait for the "A ended" signal.
            $result['b_active_after_a_end'] = $manager->hasActiveContext();
            $channelB->push('done');
        });

        $channelA->pop(); // A started.
        $channelB->pop(); // B started.

        $this->assertNotSame($result['a_id'], $result['b_id']);
        $this->assertTrue($result['a_active']);
        $this->assertTrue($result['b_active']);

        $channelA->push('end');
        $channelA->pop(); // A finished ending its context.

        $channelB->push('check');
        $channelB->pop(); // B verified its own context is still active.

        $this->assertFalse($result['a_active_after_end']);
        $this->assertTrue($result['b_active_after_a_end']);
    }

    public function testStartContextIsIdempotentWithinSameCoroutine(): void
    {
        $manager = $this->createRuntimeContextManager();

        $manager->startContext();
        $firstId = $manager->getCurrentContext()->getId();
        $manager->startContext();

        // A nested start for the same execution key is a no-op.
        $this->assertSame($firstId, $manager->getCurrentContext()->getId());
        $this->assertTrue($manager->hasActiveContext());

        $manager->endContext();

        $this->assertFalse($manager->hasActiveContext());
    }

    private function createRuntimeContextManager(): RuntimeContextManager
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('getOptions')->andReturn(new Options());
        $client->shouldReceive('flush')->andReturn(new Result(ResultStatus::success()));

        $hub = Mockery::mock(HubInterface::class);
        $hub->shouldReceive('getClient')->andReturn($client);

        return new RuntimeContextManager($hub);
    }
}
