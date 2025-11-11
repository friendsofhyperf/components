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
class CallQueuedClosureCreateTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testCreateMethodWithDefaultMaxAttempts()
    {
        $closure = function () {
            return 'default max attempts';
        };

        $job = CallQueuedClosure::create($closure);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testCreateMethodWithCustomMaxAttempts()
    {
        $closure = function () {
            return 'custom max attempts';
        };

        $job = CallQueuedClosure::create($closure, 7);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(7, $job->getMaxAttempts());
    }

    public function testCreateMethodWithZeroMaxAttempts()
    {
        $closure = function () {
            return 'zero max attempts';
        };

        $job = CallQueuedClosure::create($closure, 0);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testCreateMethodWithNegativeMaxAttempts()
    {
        $closure = function () {
            return 'negative max attempts';
        };

        $job = CallQueuedClosure::create($closure, -1);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(-1, $job->getMaxAttempts());
    }

    public function testCreateMethodWithLargeMaxAttempts()
    {
        $closure = function () {
            return 'large max attempts';
        };

        $job = CallQueuedClosure::create($closure, 999);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(999, $job->getMaxAttempts());
    }

    public function testCreateMethodPreservesClosureFunctionality()
    {
        $testValue = null;
        $closure = function () use (&$testValue) {
            $testValue = 'closure executed';
            return 'result';
        };

        $job = CallQueuedClosure::create($closure, 5);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(5, $job->getMaxAttempts());

        // Mock the container
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        // Execute the job
        $result = $job->handle();

        $this->assertEquals('closure executed', $testValue);
        $this->assertEquals('result', $result);
    }

    public function testCreateMethodWithParameters()
    {
        $receivedParam = null;
        $closure = function ($param = 'default') use (&$receivedParam) {
            $receivedParam = $param;
        };

        $job = CallQueuedClosure::create($closure, 2);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(2, $job->getMaxAttempts());

        // Mock the container
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        // Execute the job
        $job->handle();

        $this->assertEquals('default', $receivedParam);
    }

    public function testCreateMethodWithDependencyInjection()
    {
        $executed = false;
        $test = $this;

        $closure = function (ContainerInterface $container) use (&$executed, $test) {
            $executed = true;
            $test->assertInstanceOf(ContainerInterface::class, $container);
        };

        $job = CallQueuedClosure::create($closure, 4);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(4, $job->getMaxAttempts());

        // Mock the container with DI support
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(true);

        $definitionCollector = m::mock(ClosureDefinitionCollectorInterface::class);
        $definition = m::mock();
        $definition->shouldReceive('getMeta')
            ->with('name')
            ->andReturn('container');
        $definition->shouldReceive('getName')
            ->andReturn(ContainerInterface::class);
        $definition->shouldReceive('getMeta')
            ->with('defaultValueAvailable')
            ->andReturn(false);
        $definition->shouldReceive('allowsNull')
            ->andReturn(false);

        $definitionCollector->shouldReceive('getParameters')
            ->andReturn([0 => $definition]);

        $container->shouldReceive('get')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn($definitionCollector);
        $container->shouldReceive('has')
            ->with(ContainerInterface::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(ContainerInterface::class)
            ->andReturn($container);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertTrue($executed);
    }

    public function testCreateMethodSerializesCorrectly()
    {
        $capturedValue = 'captured for serialization';
        $closure = function () use ($capturedValue) {
            return $capturedValue;
        };

        $job = CallQueuedClosure::create($closure, 6);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(6, $job->getMaxAttempts());

        // Test serialization
        $serialized = serialize($job);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(CallQueuedClosure::class, $unserialized);
        $this->assertEquals(6, $unserialized->getMaxAttempts());

        // Mock the container for execution
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        // Execute the unserialized job
        $result = $unserialized->handle();

        $this->assertEquals('captured for serialization', $result);
    }

    public function testCreateMethodMethodProperty()
    {
        $closure = function () {
            return 'method property test';
        };

        $job = CallQueuedClosure::create($closure, 8);

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(8, $job->getMaxAttempts());
        $this->assertStringContainsString('CallQueuedClosureCreateTest.php', $job->method);
        $this->assertStringContainsString(':', $job->method);
    }

    public function testMultipleCreateCallsWithDifferentMaxAttempts()
    {
        $closure1 = function () { return 'first'; };
        $closure2 = function () { return 'second'; };
        $closure3 = function () { return 'third'; };

        $job1 = CallQueuedClosure::create($closure1, 1);
        $job2 = CallQueuedClosure::create($closure2, 5);
        $job3 = CallQueuedClosure::create($closure3, 10);

        $this->assertEquals(1, $job1->getMaxAttempts());
        $this->assertEquals(5, $job2->getMaxAttempts());
        $this->assertEquals(10, $job3->getMaxAttempts());

        $this->assertNotSame($job1, $job2);
        $this->assertNotSame($job2, $job3);
        $this->assertNotSame($job1, $job3);
    }
}