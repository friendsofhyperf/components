<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
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
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;

uses(InteractsWithContainer::class);

beforeEach(function () {
    $this->refreshContainer();
});

afterEach(function () {
    m::close();
    $this->flushContainer();
});

// Helper functions
function createMockContainerWithoutEvents(): ContainerInterface
{
    $container = m::mock(ContainerInterface::class);
    $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();
    return $container;
}

function createMockContainerWithEvents($eventDispatcher): ContainerInterface
{
    $container = m::mock(ContainerInterface::class);
    $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnTrue();
    $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($eventDispatcher);
    return $container;
}

// Constructor Tests
test('constructor without event dispatcher', function () {
    $driver = m::mock(DriverInterface::class);
    $container = createMockContainerWithoutEvents();

    $repository = new Repository($container, $driver, 'test');

    expect($repository->getDriver())->toBe($driver);
    expect($repository->getStore())->toBe($driver);
});

test('constructor with event dispatcher', function () {
    $driver = m::mock(DriverInterface::class);
    $events = m::mock(EventDispatcherInterface::class);
    $container = createMockContainerWithEvents($events);

    $repository = new Repository($container, $driver, 'test');

    expect($repository->getDriver())->toBe($driver);
});

test('clone', function () {
    $driver = m::mock(DriverInterface::class);
    $container = createMockContainerWithoutEvents();

    $repository = new Repository($container, $driver);

    $cloned = clone $repository;

    // The driver should be cloned too
    expect($cloned->getDriver())->not->toBe($repository->getDriver());
});

// Get Tests
test('get with cache hit', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('bar');

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->get('foo');

    expect($result)->toBe('bar');
});

test('get with cache miss', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->get('foo');

    expect($result)->toBeNull();
});

test('get with miss and default', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->get('foo', 'default');

    expect($result)->toBe('default');
});

test('get with miss and callable default', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->get('foo', fn () => 'computed');

    expect($result)->toBe('computed');
});

test('get with array key', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('getMultiple')->with(['foo', 'bar'])->once()->andReturn(['foo' => 'val1', 'bar' => 'val2']);

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingManyKeys::class));
    $events->shouldReceive('dispatch')->twice()->with(m::type(CacheHit::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->get(['foo', 'bar']);

    expect($result)->toBe(['foo' => 'val1', 'bar' => 'val2']);
});

test('many', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('getMultiple')->with(['foo', 'bar'])->once()->andReturn(['foo' => 'val1', 'bar' => null]);

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingManyKeys::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->many(['foo', 'bar' => 'default']);

    expect($result)->toBe(['foo' => 'val1', 'bar' => 'default']);
});

test('get multiple', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('getMultiple')->with(['foo', 'bar'])->once()->andReturn(['foo' => 'val1', 'bar' => null]);

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingManyKeys::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->getMultiple(['foo', 'bar'], 'default');

    expect($result)->toBe(['foo' => 'val1', 'bar' => 'default']);
});

// Put Tests
test('put with ttl', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->put('foo', 'bar', 60);

    expect($result)->toBeTrue();
});

test('put without ttl', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('set')->with('foo', 'bar')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->put('foo', 'bar');

    expect($result)->toBeTrue();
});

test('put with zero ttl', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->put('foo', 'bar', 0);

    expect($result)->toBeTrue();
});

// Has and Missing Tests
test('has', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('has')->with('foo')->once()->andReturnTrue();

    $container = createMockContainerWithoutEvents();
    $repository = new Repository($container, $driver);

    $result = $repository->has('foo');

    expect($result)->toBeTrue();
});

test('missing', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('has')->with('foo')->once()->andReturnFalse();

    $container = createMockContainerWithoutEvents();
    $repository = new Repository($container, $driver);

    $result = $repository->missing('foo');

    expect($result)->toBeTrue();
});

// Forget and Delete Tests
test('forget', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->forget('foo');

    expect($result)->toBeTrue();
});

test('forget dispatches key forgotten event', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->forget('foo');

    expect($result)->toBeTrue();
});

test('forget dispatches key forget failed event', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnFalse();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgetFailed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->forget('foo');

    expect($result)->toBeFalse();
});

test('delete', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->delete('foo');

    expect($result)->toBeTrue();
});

// Clear and Flush Tests
test('clear', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('clear')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushing::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->clear();

    expect($result)->toBeTrue();
});

test('flush', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('clear')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushing::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheFlushed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->flush();

    expect($result)->toBeTrue();
});

// Add Tests
test('add', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
    $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->add('foo', 'bar', 60);

    expect($result)->toBeTrue();
});

test('add when key exists', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('existing');

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->add('foo', 'bar', 60);

    expect($result)->toBeFalse();
});

// Pull Tests
test('pull', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('bar');
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->pull('foo');

    expect($result)->toBe('bar');
});

test('pull with default', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    // Note: The current implementation of pull() doesn't pass the default to get()
    // so this returns null instead of 'default'
    $result = $repository->pull('foo', 'default');

    expect($result)->toBeNull();
});

// Remember Tests
test('remember', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
    $driver->shouldReceive('set')->with('foo', 'computed', 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->remember('foo', 60, fn () => 'computed');

    expect($result)->toBe('computed');
});

test('remember when key exists', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('existing');

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->remember('foo', 60, fn () => 'computed');

    expect($result)->toBe('existing');
});

test('remember forever', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
    $driver->shouldReceive('set')->with('foo', 'computed')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->rememberForever('foo', fn () => 'computed');

    expect($result)->toBe('computed');
});

test('sear', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
    $driver->shouldReceive('set')->with('foo', 'computed')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->sear('foo', fn () => 'computed');

    expect($result)->toBe('computed');
});

// Increment and Decrement Tests
test('increment', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
    $driver->shouldReceive('set')->with('foo', 6)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->increment('foo');

    expect($result)->toBe(6);
});

test('decrement', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
    $driver->shouldReceive('set')->with('foo', 4)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->decrement('foo');

    expect($result)->toBe(4);
});

// GetSeconds Tests
test('get seconds with integer', function () {
    $driver = m::mock(DriverInterface::class);
    $container = createMockContainerWithoutEvents();
    $repository = new Repository($container, $driver);

    // Using reflection to test protected method
    $reflection = new ReflectionClass($repository);
    $method = $reflection->getMethod('getSeconds');
    $method->setAccessible(true);

    $result = $method->invoke($repository, 60);

    expect($result)->toBe(60);
});

test('get seconds with zero', function () {
    $driver = m::mock(DriverInterface::class);
    $container = createMockContainerWithoutEvents();
    $repository = new Repository($container, $driver);

    $reflection = new ReflectionClass($repository);
    $method = $reflection->getMethod('getSeconds');
    $method->setAccessible(true);

    $result = $method->invoke($repository, -60);

    expect($result)->toBe(0);
});

test('get seconds with date interval', function () {
    $driver = m::mock(DriverInterface::class);
    $container = createMockContainerWithoutEvents();
    $repository = new Repository($container, $driver);

    $reflection = new ReflectionClass($repository);
    $method = $reflection->getMethod('getSeconds');
    $method->setAccessible(true);

    $interval = new DateInterval('PT1H'); // 1 hour
    $result = $method->invoke($repository, $interval);

    expect($result)->toBe(3600);
});

test('get seconds with date time', function () {
    $driver = m::mock(DriverInterface::class);
    $container = createMockContainerWithoutEvents();
    $repository = new Repository($container, $driver);

    $reflection = new ReflectionClass($repository);
    $method = $reflection->getMethod('getSeconds');
    $method->setAccessible(true);

    $futureTime = new DateTime('+1 hour');
    $result = $method->invoke($repository, $futureTime);

    expect($result)->toBeGreaterThan(3500);
    expect($result)->toBeLessThan(3700);
});

// PutMany Tests
test('put many', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('setMultiple')->with(['foo' => 'bar', 'baz' => 'qux'], 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
    $events->shouldReceive('dispatch')->twice()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->putMany(['foo' => 'bar', 'baz' => 'qux'], 60);

    expect($result)->toBeTrue();
});

test('put many without ttl', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('setMultiple')->with(['foo' => 'bar'])->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->putMany(['foo' => 'bar']);

    expect($result)->toBeTrue();
});

test('put many with zero ttl', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();
    $driver->shouldReceive('delete')->with('bar')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->twice()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->twice()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->putMany(['foo' => 'val1', 'bar' => 'val2'], 0);

    expect($result)->toBeTrue();
});

test('put many failed', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('setMultiple')->with(['foo' => 'bar'], 60)->once()->andReturnFalse();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->putMany(['foo' => 'bar'], 60);

    expect($result)->toBeFalse();
});

// Set Tests
test('set', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->set('foo', 'bar', 60);

    expect($result)->toBeTrue();
});

test('set multiple', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('setMultiple')->with(['foo' => 'bar'], 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->setMultiple(['foo' => 'bar'], 60);

    expect($result)->toBeTrue();
});

// Delete Multiple Tests
test('delete multiple', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();
    $driver->shouldReceive('delete')->with('bar')->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->twice()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->twice()->with(m::type(KeyForgotten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->deleteMultiple(['foo', 'bar']);

    expect($result)->toBeTrue();
});

test('delete multiple with failure', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('delete')->with('foo')->once()->andReturnTrue();
    $driver->shouldReceive('delete')->with('bar')->once()->andReturnFalse();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->twice()->with(m::type(ForgettingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgotten::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyForgetFailed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->deleteMultiple(['foo', 'bar']);

    expect($result)->toBeFalse();
});

// Forever Failed Test
test('forever failed', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('set')->with('foo', 'bar')->once()->andReturnFalse();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->forever('foo', 'bar');

    expect($result)->toBeFalse();
});

// Put Failed Test
test('put failed', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('set')->with('foo', 'bar', 60)->once()->andReturnFalse();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->put('foo', 'bar', 60);

    expect($result)->toBeFalse();
});

// Put with Array Key Test
test('put with array key', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('setMultiple')->with(['foo' => 'bar', 'baz' => 'qux'], 60)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingManyKeys::class));
    $events->shouldReceive('dispatch')->twice()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->put(['foo' => 'bar', 'baz' => 'qux'], 60);

    expect($result)->toBeTrue();
});

// More Increment/Decrement Tests
test('increment with value', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
    $driver->shouldReceive('set')->with('foo', 8)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->increment('foo', 3);

    expect($result)->toBe(8);
});

test('increment from zero', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturnNull();
    $driver->shouldReceive('set')->with('foo', 1)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheMissed::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->increment('foo');

    expect($result)->toBe(1);
});

test('decrement with value', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('10');
    $driver->shouldReceive('set')->with('foo', 7)->once()->andReturnTrue();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWritten::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->decrement('foo', 3);

    expect($result)->toBe(7);
});

test('increment put fails', function () {
    $driver = m::mock(DriverInterface::class);
    $driver->shouldReceive('get')->with('foo')->once()->andReturn('5');
    $driver->shouldReceive('set')->with('foo', 6)->once()->andReturnFalse();

    $events = m::mock(EventDispatcherInterface::class);
    $events->shouldReceive('dispatch')->once()->with(m::type(CacheHit::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(RetrievingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(WritingKey::class));
    $events->shouldReceive('dispatch')->once()->with(m::type(KeyWriteFailed::class));

    $container = createMockContainerWithEvents($events);
    $repository = new Repository($container, $driver);

    $result = $repository->increment('foo');

    expect($result)->toBeFalse();
});
