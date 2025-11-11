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
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Mockery as m;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('async-queue-closure-job')]
class BasicFunctionalityTest extends TestCase
{
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

        $job = CallQueuedClosure::create($closure, 5);

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

    public function testCallQueuedClosureCreateWithZeroMaxAttempts()
    {
        $closure = function () {
            return 'test';
        };

        $job = CallQueuedClosure::create($closure, 0);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testCallQueuedClosureCreateWithNegativeMaxAttempts()
    {
        $closure = function () {
            return 'test';
        };

        $job = CallQueuedClosure::create($closure, -1);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(-1, $job->getMaxAttempts());
    }

    public function testCallQueuedClosureJobExecution()
    {
        $executed = false;

        $job = CallQueuedClosure::create(function () use (&$executed) {
            $executed = true;
            return 'executed';
        }, 3);

        $this->assertEquals(3, $job->getMaxAttempts());

        // Mock container for execution
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $result = $job->handle();

        $this->assertTrue($executed);
        $this->assertEquals('executed', $result);
    }

    public function testCallQueuedClosureWithReturnValue()
    {
        $job = CallQueuedClosure::create(function () {
            return 'return value';
        }, 2);

        // Mock container for execution
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $result = $job->handle();

        $this->assertEquals('return value', $result);
        $this->assertEquals(2, $job->getMaxAttempts());
    }

    public function testCallQueuedClosureWithParameters()
    {
        $receivedParam = null;

        $job = CallQueuedClosure::create(function ($param = 'default') use (&$receivedParam) {
            $receivedParam = $param;
        }, 4);

        // Mock container for execution
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertEquals('default', $receivedParam);
        $this->assertEquals(4, $job->getMaxAttempts());
    }

    public function testCallQueuedClosureSerialization()
    {
        $capturedValue = 'captured for serialization';

        $job = CallQueuedClosure::create(function () use ($capturedValue) {
            return $capturedValue;
        }, 6);

        $this->assertEquals(6, $job->getMaxAttempts());

        // Test serialization
        $serialized = serialize($job);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(CallQueuedClosure::class, $unserialized);
        $this->assertEquals(6, $unserialized->getMaxAttempts());

        // Mock container for execution
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        // Execute the unserialized job
        $result = $unserialized->handle();

        $this->assertEquals('captured for serialization', $result);
    }

    public function testCallQueuedClosureMethodProperty()
    {
        $job = CallQueuedClosure::create(function () {
            return 'method property test';
        }, 8);

        $this->assertEquals(8, $job->getMaxAttempts());
        $this->assertStringContainsString('BasicFunctionalityTest.php', $job->method);
        $this->assertStringContainsString(':', $job->method);
    }

    public function testMultipleCreateCallsWithDifferentMaxAttempts()
    {
        $closure = function () { return 'test'; };

        $job1 = CallQueuedClosure::create($closure, 1);
        $job2 = CallQueuedClosure::create($closure, 5);
        $job3 = CallQueuedClosure::create($closure, 10);
        $job4 = CallQueuedClosure::create($closure, 0);
        $job5 = CallQueuedClosure::create($closure, -1);

        $this->assertEquals(1, $job1->getMaxAttempts());
        $this->assertEquals(5, $job2->getMaxAttempts());
        $this->assertEquals(10, $job3->getMaxAttempts());
        $this->assertEquals(0, $job4->getMaxAttempts());
        $this->assertEquals(-1, $job5->getMaxAttempts());

        $this->assertNotSame($job1, $job2);
        $this->assertNotSame($job2, $job3);
        $this->assertNotSame($job1, $job3);
    }

    // Note: Dispatch function tests are omitted due to container configuration requirements
    // The functionality is tested through PendingClosureDispatch direct instantiation

    /**
     * Helper method to get protected/private property value.
     */
    protected function getProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}