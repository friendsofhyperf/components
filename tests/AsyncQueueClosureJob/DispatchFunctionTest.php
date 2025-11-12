<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\AsyncQueueClosureJob;

use FriendsOfHyperf\AsyncQueueClosureJob\CallQueuedClosure;
use FriendsOfHyperf\AsyncQueueClosureJob\PendingClosureDispatch;
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\AsyncQueue\Driver\Driver;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;
use Mockery as m;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('async-queue-closure-job')]
class DispatchFunctionTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testDispatchFunctionWithDefaultMaxAttempts()
    {
        // Mock the container and driver factory to prevent actual dispatch
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $closure = function () {
            return 'test';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has default maxAttempts (0)
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testDispatchFunctionWithCustomMaxAttempts()
    {
        // Mock the container and driver factory to prevent actual dispatch
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $closure = function () {
            return 'test with max attempts';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure, 5);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has the specified maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(5, $job->getMaxAttempts());
    }

    public function testDispatchFunctionWithFluentConfiguration()
    {
        $executed = false;

        // Mock the container and driver factory
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('high-priority')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->once()
            ->with(m::type(CallQueuedClosure::class), 30)
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $closure = function () use (&$executed) {
            $executed = true;
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure, 3)
            ->onConnection('high-priority')
            ->delay(30);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has the specified maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(3, $job->getMaxAttempts());

        // Trigger destruct to verify dispatch
        unset($dispatch);
        $this->assertFalse($executed); // Job should be queued, not executed immediately
    }

    public function testDispatchFunctionChainingWithMaxAttempts()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('delayed-connection')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->once()
            ->with(m::type(CallQueuedClosure::class), 120)
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $closure = function () {
            return 'chained dispatch';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure, 2)
            ->onConnection('delayed-connection')
            ->delay(120)
            ->setMaxAttempts(5); // Override the initial maxAttempts

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has the overridden maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(5, $job->getMaxAttempts()); // Should be overridden to 5

        // Trigger destruct
        unset($dispatch);
    }

    public function testDispatchFunctionWithZeroMaxAttempts()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $closure = function () {
            return 'zero attempts';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure, 0);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has zero maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testDispatchFunctionConditionableMethods()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('conditional-connection')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->once()
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $closure = function () {
            return 'conditional';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure, 1)
            ->when(true, function ($dispatch) {
                $dispatch->onConnection('conditional-connection');
            })
            ->unless(false, function ($dispatch) {
                $dispatch->delay(45);
            });

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify configuration
        $this->assertEquals('conditional-connection', $this->getProperty($dispatch, 'connection'));
        $this->assertEquals(45, $this->getProperty($dispatch, 'delay'));

        // Verify the job has the specified maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(1, $job->getMaxAttempts());

        unset($dispatch);
    }

    /**
     * Helper method to get protected/private property value.
     */
    protected function getProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
