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
use FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch;
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
class CoreFunctionalityTest extends TestCase
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

    public function testCallQueuedClosureCreateWithMaxAttempts()
    {
        $closure = function () {
            return 'test';
        };

        $job = CallQueuedClosure::create($closure);
        $job->setMaxAttempts(5);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(5, $job->getMaxAttempts());
    }

    public function testCallQueuedClosureCreateWithDefaultMaxAttempts()
    {
        $closure = function () {
            return 'test';
        };

        $job = CallQueuedClosure::create($closure);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testCallQueuedClosureConstructorWithMaxAttempts()
    {
        $closure = function () {
            return 'test';
        };

        $job = CallQueuedClosure::create($closure);
        $job->setMaxAttempts(3);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(3, $job->getMaxAttempts());
    }

    public function testPendingAsyncQueueDispatchConfiguration()
    {
        $job = CallQueuedClosure::create(function () {
            return 'test';
        });

        $dispatch = new PendingAsyncQueueDispatch($job);

        // Test configuration methods
        $result = $dispatch->onPool('test-connection')
            ->delay(30)
            ->setMaxAttempts(7);

        $this->assertSame($dispatch, $result);
        $this->assertEquals('test-connection', $this->getProperty($dispatch, 'pool'));
        $this->assertEquals(30, $this->getProperty($dispatch, 'delay'));
        $this->assertEquals(7, $job->getMaxAttempts());
    }

    public function testDispatchFunctionCreatesCorrectObjects()
    {
        $closure = function () {
            return 'dispatch test';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure);
        $dispatch->setMaxAttempts(4);

        $this->assertInstanceOf(PendingAsyncQueueDispatch::class, $dispatch);

        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(4, $job->getMaxAttempts());
    }

    public function testDispatchFunctionWithDefaultMaxAttempts()
    {
        $closure = function () {
            return 'dispatch test';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure);

        $this->assertInstanceOf(PendingAsyncQueueDispatch::class, $dispatch);

        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testFluentMethodChaining()
    {
        $closure = function () {
            return 'chaining test';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onPool('chained-connection')
            ->delay(60)
            ->setMaxAttempts(8); // Override initial value

        $this->assertEquals('chained-connection', $this->getProperty($dispatch, 'pool'));
        $this->assertEquals(60, $this->getProperty($dispatch, 'delay'));

        $job = $this->getProperty($dispatch, 'job');
        $this->assertEquals(8, $job->getMaxAttempts());
    }

    public function testConditionableTraitFunctionality()
    {
        $closure = function () {
            return 'conditionable test';
        };

        // Test when() with true condition
        $dispatch1 = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->when(true, function ($dispatch) {
                $dispatch->onPool('when-true-connection');
            });

        $this->assertEquals('when-true-connection', $this->getProperty($dispatch1, 'pool'));

        // Test when() with false condition
        $dispatch2 = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->when(false, function ($dispatch) {
                $dispatch->onPool('when-false-connection');
            });

        $this->assertEquals('default', $this->getProperty($dispatch2, 'pool'));

        // Test unless() with true condition
        $dispatch3 = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->unless(true, function ($dispatch) {
                $dispatch->onPool('unless-true-connection');
            });

        $this->assertEquals('default', $this->getProperty($dispatch3, 'pool'));

        // Test unless() with false condition
        $dispatch4 = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->unless(false, function ($dispatch) {
                $dispatch->onPool('unless-false-connection');
            });

        $this->assertEquals('unless-false-connection', $this->getProperty($dispatch4, 'pool'));
    }

    public function testJobExecutionWithMaxAttempts()
    {
        $executed = false;

        $job = CallQueuedClosure::create(function () use (&$executed) {
            $executed = true;
            return 'executed';
        });
        $job->setMaxAttempts(6);

        $this->assertEquals(6, $job->getMaxAttempts());

        // Mock container for execution
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(\Hyperf\Di\ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $result = $job->handle();

        $this->assertTrue($executed);
        $this->assertEquals('executed', $result);
    }

    public function testMultipleMaxAttemptsValues()
    {
        $closure = function () { return 'test'; };

        $job1 = CallQueuedClosure::create($closure);
        $job1->setMaxAttempts(1);
        $job2 = CallQueuedClosure::create($closure);
        $job2->setMaxAttempts(5);
        $job3 = CallQueuedClosure::create($closure);
        $job3->setMaxAttempts(10);
        $job4 = CallQueuedClosure::create($closure);
        $job4->setMaxAttempts(0);
        $job5 = CallQueuedClosure::create($closure);
        $job5->setMaxAttempts(-1);

        $this->assertEquals(1, $job1->getMaxAttempts());
        $this->assertEquals(5, $job2->getMaxAttempts());
        $this->assertEquals(10, $job3->getMaxAttempts());
        $this->assertEquals(0, $job4->getMaxAttempts());
        $this->assertEquals(-1, $job5->getMaxAttempts());
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
