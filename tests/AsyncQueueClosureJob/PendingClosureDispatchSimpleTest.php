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
class PendingClosureDispatchSimpleTest extends TestCase
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
            ->zeroOrMoreTimes()
            ->byDefault()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->byDefault()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->zeroOrMoreTimes()
            ->byDefault()
            ->andReturnTrue();

        ApplicationContext::setContainer($container);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testPendingClosureDispatchCanBeCreated()
    {
        $job = CallQueuedClosure::create(function () {
            return 'test';
        });

        $dispatch = new PendingClosureDispatch($job);

        $this->assertInstanceOf(PendingClosureDispatch::class, $dispatch);
        $this->assertSame($job, $this->getProperty($dispatch, 'job'));
    }

    public function testSetMaxAttemptsMethod()
    {
        $job = m::mock(CallQueuedClosure::class);
        $job->shouldReceive('setMaxAttempts')
            ->once()
            ->with(5);

        $dispatch = new PendingClosureDispatch($job);
        $result = $dispatch->setMaxAttempts(5);

        $this->assertSame($dispatch, $result);
    }

    public function testonConnectionMethod()
    {
        $job = m::mock(CallQueuedClosure::class);

        $dispatch = new PendingClosureDispatch($job);
        $result = $dispatch->onConnection('high-priority');

        $this->assertSame($dispatch, $result);
        $this->assertEquals('high-priority', $this->getProperty($dispatch, 'connection'));
    }

    public function testDelayMethod()
    {
        $job = m::mock(CallQueuedClosure::class);

        $dispatch = new PendingClosureDispatch($job);
        $result = $dispatch->delay(60);

        $this->assertSame($dispatch, $result);
        $this->assertEquals(60, $this->getProperty($dispatch, 'delay'));
    }

    public function testMethodChaining()
    {
        $job = m::mock(CallQueuedClosure::class);
        $job->shouldReceive('setMaxAttempts')
            ->once()
            ->with(3);

        $dispatch = new PendingClosureDispatch($job);
        $result = $dispatch->onConnection('default')
            ->delay(30)
            ->setMaxAttempts(3);

        $this->assertSame($dispatch, $result);
        $this->assertEquals('default', $this->getProperty($dispatch, 'connection'));
        $this->assertEquals(30, $this->getProperty($dispatch, 'delay'));
    }

    public function testDefaultValues()
    {
        $job = m::mock(CallQueuedClosure::class);
        $dispatch = new PendingClosureDispatch($job);

        $this->assertEquals('default', $this->getProperty($dispatch, 'connection'));
        $this->assertEquals(0, $this->getProperty($dispatch, 'delay'));
    }

    public function testConditionableTrait()
    {
        $job = m::mock(CallQueuedClosure::class);

        $dispatch = new PendingClosureDispatch($job);

        // Test when() method from Conditionable trait
        $result = $dispatch->when(true, function ($dispatch) {
            $dispatch->onConnection('conditional-connection');
        });

        $this->assertSame($dispatch, $result);
        $this->assertEquals('conditional-connection', $this->getProperty($dispatch, 'connection'));

        // Test when() with false condition
        $dispatch2 = new PendingClosureDispatch($job);
        $result2 = $dispatch2->when(false, function ($dispatch) {
            $dispatch->onConnection('should-not-change');
        });

        $this->assertSame($dispatch2, $result2);
        $this->assertEquals('default', $this->getProperty($dispatch2, 'connection'));
    }

    public function testUnlessMethodFromConditionableTrait()
    {
        $job = m::mock(CallQueuedClosure::class);

        $dispatch = new PendingClosureDispatch($job);

        // Test unless() method from Conditionable trait
        $result = $dispatch->unless(false, function ($dispatch) {
            $dispatch->onConnection('unless-connection');
        });

        $this->assertSame($dispatch, $result);
        $this->assertEquals('unless-connection', $this->getProperty($dispatch, 'connection'));

        // Test unless() with true condition
        $dispatch2 = new PendingClosureDispatch($job);
        $result2 = $dispatch2->unless(true, function ($dispatch) {
            $dispatch->onConnection('should-not-connection');
        });

        $this->assertSame($dispatch2, $result2);
        $this->assertEquals('default', $this->getProperty($dispatch2, 'connection'));
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
