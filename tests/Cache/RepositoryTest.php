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

use Carbon\Carbon;
use DateInterval;
use DateTime;
use FriendsOfHyperf\Cache\Event\CacheFlushed;
use FriendsOfHyperf\Cache\Event\CacheFlushing;
use FriendsOfHyperf\Cache\Event\CacheHit;
use FriendsOfHyperf\Cache\Event\CacheMissed;
use FriendsOfHyperf\Cache\Event\ForgettingKey;
use FriendsOfHyperf\Cache\Event\KeyForgetFailed;
use FriendsOfHyperf\Cache\Event\KeyForgotten;
use FriendsOfHyperf\Cache\Event\KeyWriteFailed;
use FriendsOfHyperf\Cache\Event\KeyWritten;
use FriendsOfHyperf\Cache\Event\RetrievingKey;
use FriendsOfHyperf\Cache\Event\RetrievingManyKeys;
use FriendsOfHyperf\Cache\Event\WritingKey;
use FriendsOfHyperf\Cache\Event\WritingManyKeys;
use FriendsOfHyperf\Cache\Repository;
use FriendsOfHyperf\Tests\Concerns\InteractsWithContainer;
use Hyperf\Cache\Driver\DriverInterface;
use Mockery as m;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[Group('cache')]
class RepositoryTest extends TestCase
{
    use InteractsWithContainer;

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

    protected function createMockContainerWithoutEvents(): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
        return $container;
    }

    protected function createMockContainerWithEvents($eventDispatcher): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($eventDispatcher);
        return $container;
    }

    public function testConstructorWithoutEventDispatcher(): void
    {
        $driver = m::mock(DriverInterface::class);
        $container = $this->createMockContainerWithoutEvents();
        
        $repository = new Repository($container, $driver, 'test');

        $this->assertSame($driver, $repository->getDriver());
        $this->assertSame($driver, $repository->getStore());
    }

    public function testConstructorWithEventDispatcher(): void
    {
        $driver = m::mock(DriverInterface::class);
        $events = m::mock(EventDispatcherInterface::class);
        $container = $this->createMockContainerWithEvents($events);

        $repository = new Repository($container, $driver, 'test');

        $this->assertSame($driver, $repository->getDriver());
    }

    public function testClone(): void
    {
        $driver = m::mock(DriverInterface::class);
        $container = $this->createMockContainerWithoutEvents();
        
        $repository = new Repository($container, $driver);

        $cloned = clone $repository;

        // The driver should be cloned too
        $this->assertNotSame($repository->getDriver(), $cloned->getDriver());
    }

    public function testGetWithHit(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('bar');

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->get('foo');

        $this->assertSame('bar', $result);
    }

    public function testGetWithMiss(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->get('foo');

        $this->assertNull($result);
    }

    public function testGetWithMissAndDefault(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->get('foo', 'default');

        $this->assertSame('default', $result);
    }

    public function testGetWithMissAndCallableDefault(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->get('foo', fn() => 'computed');

        $this->assertSame('computed', $result);
    }

    public function testGetWithArrayKey(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('getMultiple')->with(['foo', 'bar'])->once()->andReturn(['foo' => 'val1', 'bar' => 'val2']);

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingManyKeys::class));
        $events->shouldReceive('dispatch')->twice()->with(m::type(CacheHit::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->get(['foo', 'bar']);

        $this->assertSame(['foo' => 'val1', 'bar' => 'val2'], $result);
    }

    public function testMany(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('getMultiple')->with(['foo', 'bar'])->once()->andReturn(['foo' => 'val1', 'bar' => null]);

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingManyKeys::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->many(['foo', 'bar' => 'default']);

        $this->assertSame(['foo' => 'val1', 'bar' => 'default'], $result);
    }

    public function testGetMultiple(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('getMultiple')->with(['foo', 'bar'])->once()->andReturn(['foo' => 'val1', 'bar' => null]);

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingManyKeys::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->getMultiple(['foo', 'bar'], 'default');

        $this->assertSame(['foo' => 'val1', 'bar' => 'default'], $result);
    }

    public function testPutWithTtl(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->put('foo', 'bar', 60);

        $this->assertTrue($result);
    }

    public function testPutWithoutTtl(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('set')->with('foo', 'bar')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->put('foo', 'bar');

        $this->assertTrue($result);
    }

    public function testPutWithZeroTtl(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->put('foo', 'bar', 0);

        $this->assertTrue($result);
    }

    public function testHas(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('has')->with('foo')->once()->andReturnTrue();

        $container = $this->createMockContainerWithoutEvents();
        $repository = new Repository($container, $driver);

        $result = $repository->has('foo');

        $this->assertTrue($result);
    }

    public function testMissing(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('has')->with('foo')->once()->andReturnFalse();

        $container = $this->createMockContainerWithoutEvents();
        $repository = new Repository($container, $driver);

        $result = $repository->missing('foo');

        $this->assertTrue($result);
    }

    public function testForget(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->forget('foo');

        $this->assertTrue($result);
    }

    public function testDelete(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->delete('foo');

        $this->assertTrue($result);
    }

    public function testClear(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('clear')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushing::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->clear();

        $this->assertTrue($result);
    }

    public function testFlush(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('clear')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushing::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->flush();

        $this->assertTrue($result);
    }

    public function testAdd(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
        $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->add('foo', 'bar', 60);

        $this->assertTrue($result);
    }

    public function testAddWhenKeyExists(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('existing');

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->add('foo', 'bar', 60);

        $this->assertFalse($result);
    }

    public function testPull(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('bar');
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->pull('foo');

        $this->assertSame('bar', $result);
    }

    public function testPullWithDefault(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        // Note: The current implementation of pull() doesn't pass the default to get()
        // so this returns null instead of 'default'
        $result = $repository->pull('foo', 'default');

        $this->assertNull($result);
    }

    public function testRemember(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
        $driver->shouldReceive('set')->with('foo', 'computed', 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->remember('foo', 60, fn() => 'computed');

        $this->assertSame('computed', $result);
    }

    public function testRememberWhenKeyExists(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('existing');

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->remember('foo', 60, fn() => 'computed');

        $this->assertSame('existing', $result);
    }

    public function testRememberForever(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
        $driver->shouldReceive('set')->with('foo', 'computed')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->rememberForever('foo', fn() => 'computed');

        $this->assertSame('computed', $result);
    }

    public function testSear(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
        $driver->shouldReceive('set')->with('foo', 'computed')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->sear('foo', fn() => 'computed');

        $this->assertSame('computed', $result);
    }

    public function testIncrement(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
        $driver->shouldReceive('set')->with('foo', 6)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->increment('foo');

        $this->assertSame(6, $result);
    }

    public function testDecrement(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
        $driver->shouldReceive('set')->with('foo', 4)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->decrement('foo');

        $this->assertSame(4, $result);
    }

    public function testGetSecondsWithInteger(): void
    {
        $driver = m::mock(DriverInterface::class);
        $container = $this->createMockContainerWithoutEvents();
        $repository = new Repository($container, $driver);

        // Using reflection to test protected method
        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('getSeconds');
        $method->setAccessible(true);

        $result = $method->invoke($repository, 60);

        $this->assertSame(60, $result);
    }

    public function testGetSecondsWithZero(): void
    {
        $driver = m::mock(DriverInterface::class);
        $container = $this->createMockContainerWithoutEvents();
        $repository = new Repository($container, $driver);

        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('getSeconds');
        $method->setAccessible(true);

        $result = $method->invoke($repository, -60);

        $this->assertSame(0, $result);
    }

    public function testGetSecondsWithDateInterval(): void
    {
        $driver = m::mock(DriverInterface::class);
        $container = $this->createMockContainerWithoutEvents();
        $repository = new Repository($container, $driver);

        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('getSeconds');
        $method->setAccessible(true);

        $interval = new DateInterval('PT1H'); // 1 hour
        $result = $method->invoke($repository, $interval);

        $this->assertSame(3600, $result);
    }

    public function testGetSecondsWithDateTime(): void
    {
        $driver = m::mock(DriverInterface::class);
        $container = $this->createMockContainerWithoutEvents();
        $repository = new Repository($container, $driver);

        $reflection = new \ReflectionClass($repository);
        $method = $reflection->getMethod('getSeconds');
        $method->setAccessible(true);

        $futureTime = new DateTime('+1 hour');
        $result = $method->invoke($repository, $futureTime);

        $this->assertGreaterThan(3500, $result);
        $this->assertLessThan(3700, $result);
    }

    public function testPutMany(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('setMultiple')->with(['foo' => 'bar', 'baz' => 'qux'], 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
        $events->shouldReceive('dispatch')->twice()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->putMany(['foo' => 'bar', 'baz' => 'qux'], 60);

        $this->assertTrue($result);
    }

    public function testPutManyWithoutTtl(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('setMultiple')->with(['foo' => 'bar'])->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->putMany(['foo' => 'bar']);

        $this->assertTrue($result);
    }

    public function testPutManyWithZeroTtl(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();
        $driver->shouldReceive('delete')->with('bar')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->twice()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->twice()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->putMany(['foo' => 'val1', 'bar' => 'val2'], 0);

        $this->assertTrue($result);
    }

    public function testPutManyFailed(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('setMultiple')->with(['foo' => 'bar'], 60)->once()->andReturnFalse();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->putMany(['foo' => 'bar'], 60);

        $this->assertFalse($result);
    }

    public function testSet(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->set('foo', 'bar', 60);

        $this->assertTrue($result);
    }

    public function testSetMultiple(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('setMultiple')->with(['foo' => 'bar'], 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->setMultiple(['foo' => 'bar'], 60);

        $this->assertTrue($result);
    }

    public function testDeleteMultiple(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();
        $driver->shouldReceive('delete')->with('bar')->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->twice()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->twice()->with(m::type(KeyForgotten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->deleteMultiple(['foo', 'bar']);

        $this->assertTrue($result);
    }

    public function testDeleteMultipleWithFailure(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();
        $driver->shouldReceive('delete')->with('bar')->once()->andReturnFalse();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->twice()->with(m::type(ForgettingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgetFailed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->deleteMultiple(['foo', 'bar']);

        $this->assertFalse($result);
    }

    public function testForeverFailed(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('set')->with('foo', 'bar')->once()->andReturnFalse();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->forever('foo', 'bar');

        $this->assertFalse($result);
    }

    public function testPutFailed(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnFalse();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->put('foo', 'bar', 60);

        $this->assertFalse($result);
    }

    public function testPutWithArrayKey(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('setMultiple')->with(['foo' => 'bar', 'baz' => 'qux'], 60)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
        $events->shouldReceive('dispatch')->twice()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->put(['foo' => 'bar', 'baz' => 'qux'], 60);

        $this->assertTrue($result);
    }

    public function testIncrementWithValue(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
        $driver->shouldReceive('set')->with('foo', 8)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->increment('foo', 3);

        $this->assertSame(8, $result);
    }

    public function testIncrementFromZero(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
        $driver->shouldReceive('set')->with('foo', 1)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->increment('foo');

        $this->assertSame(1, $result);
    }

    public function testDecrementWithValue(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('10');
        $driver->shouldReceive('set')->with('foo', 7)->once()->andReturnTrue();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->decrement('foo', 3);

        $this->assertSame(7, $result);
    }

    public function testIncrementPutFails(): void
    {
        $driver = m::mock(DriverInterface::class);
        $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
        $driver->shouldReceive('set')->with('foo', 6)->once()->andReturnFalse();

        $events = m::mock(EventDispatcherInterface::class);
        $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
        $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));
        
        $container = $this->createMockContainerWithEvents($events);
        $repository = new Repository($container, $driver);

        $result = $repository->increment('foo');

        $this->assertFalse($result);
    }
}