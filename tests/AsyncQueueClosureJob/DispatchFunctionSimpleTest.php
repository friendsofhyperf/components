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
class DispatchFunctionSimpleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up proper container mocking to prevent DirectoryNotFoundException
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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testDispatchFunctionWithDefaultMaxAttempts()
    {
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
        $closure = function () {
            return 'test with max attempts';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure);
        $dispatch->setMaxAttempts(5);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has the specified maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(5, $job->getMaxAttempts());
    }

    public function testDispatchFunctionWithZeroMaxAttempts()
    {
        $closure = function () {
            return 'zero attempts';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure);
        $dispatch->setMaxAttempts(0);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has zero maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testDispatchFunctionChainingWithMaxAttempts()
    {
        $closure = function () {
            return 'chained dispatch';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onConnection('delayed-connection')
            ->delay(120)
            ->setMaxAttempts(5); // Override the initial maxAttempts

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify the job has the overridden maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(5, $job->getMaxAttempts()); // Should be overridden to 5

        // Verify fluent configuration
        $this->assertEquals('delayed-connection', $this->getProperty($dispatch, 'connection'));
        $this->assertEquals(120, $this->getProperty($dispatch, 'delay'));
    }

    public function testDispatchFunctionConditionableMethods()
    {
        $closure = function () {
            return 'conditional';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->when(true, function ($dispatch) {
                $dispatch->onConnection('conditional-connection');
            })
            ->unless(false, function ($dispatch) {
                $dispatch->delay(45);
            })
            ->setMaxAttempts(1);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);

        // Verify configuration
        $this->assertEquals('conditional-connection', $this->getProperty($dispatch, 'connection'));
        $this->assertEquals(45, $this->getProperty($dispatch, 'delay'));

        // Verify the job has the specified maxAttempts
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(1, $job->getMaxAttempts());
    }

    public function testDispatchFunctionWithMultipleConfigurations()
    {
        $closure = function () {
            return 'multi-config test';
        };

        // Test multiple method calls and overrides
        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onConnection('initial-connection')
            ->delay(60)
            ->setMaxAttempts(8) // Override maxAttempts
            ->onConnection('multi-config-connection') // Override connection
            ->delay(300) // Override delay
            ->setMaxAttempts(10); // Final maxAttempts

        $this->assertEquals('multi-config-connection', $this->getProperty($dispatch, 'connection'));
        $this->assertEquals(300, $this->getProperty($dispatch, 'delay'));

        $job = $this->getProperty($dispatch, 'job');
        $this->assertEquals(10, $job->getMaxAttempts());
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
