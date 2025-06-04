<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Cache;

use FriendsOfHyperf\Cache\Event\ForgettingKey;
use FriendsOfHyperf\Cache\Event\KeyForgetFailed;
use FriendsOfHyperf\Cache\Event\KeyForgotten;
use FriendsOfHyperf\Cache\Repository;
use FriendsOfHyperf\Tests\Concerns\InteractsWithContainer;
use FriendsOfHyperf\Tests\Concerns\RunTestsInCoroutine;
use Hyperf\Cache\Driver\DriverInterface;
use Mockery as m;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[Group('cache')]
class RepositoryEventTest extends TestCase
{
    use InteractsWithContainer;
    use RunTestsInCoroutine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshContainer();
    }

    protected function tearDown(): void
    {
        m::close();
        $this->flushContainer();
        parent::tearDown();
    }

    public function testForgetDispatchesKeyForgottenEvent(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        $this->instance(EventDispatcherInterface::class, $events);

        $repository = new Repository($this->container, $driver);

        $result = $repository->forget('foo');

        $this->assertTrue($result);
    }

    public function testForgetDispatchesKeyForgetFailedEvent(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnFalse();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgetFailed::class));
        $this->instance(EventDispatcherInterface::class, $events);

        $repository = new Repository($this->container, $driver);

        $result = $repository->forget('foo');

        $this->assertFalse($result);
    }
}
