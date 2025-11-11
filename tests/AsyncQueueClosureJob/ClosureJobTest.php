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
class ClosureJobTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testClosureJobCanBeCreated()
    {
        $job = new CallQueuedClosure(function () {
            return 'test';
        });

        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals('Closure', $job->class);
        $this->assertIsString($job->method);
    }

    public function testClosureJobCanBeCreatedWithMaxAttempts()
    {
        $job = new CallQueuedClosure(function () {
            return 'test';
        }, 3);

        $this->assertEquals(3, $job->getMaxAttempts());
    }

    public function testClosureJobCanHandleSimpleClosure()
    {
        $executed = false;

        $job = new CallQueuedClosure(function () use (&$executed) {
            $executed = true;
        });

        // Mock the container
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertTrue($executed);
    }

    public function testClosureJobCanHandleClosureWithReturnValue()
    {
        $result = null;

        $job = new CallQueuedClosure(function () use (&$result) {
            $result = 'success';
            return $result;
        });

        // Mock the container
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertEquals('success', $result);
    }

    public function testClosureJobCanHandleClosureWithParameters()
    {
        $value = null;

        $job = new CallQueuedClosure(function ($param = 'default') use (&$value) {
            $value = $param;
        });

        // Mock the container
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertEquals('default', $value);
    }

    public function testClosureJobMethodContainsFileAndLine()
    {
        $job = new CallQueuedClosure(function () {
            return 'test';
        });

        $this->assertStringContainsString(':', $job->method);
        $this->assertStringContainsString('ClosureJobTest.php', $job->method);
    }

    public function testClosureJobWithDependencyInjection()
    {
        $executed = false;
        $test = $this;

        $job = new CallQueuedClosure(function (ContainerInterface $container) use (&$executed, $test) {
            $executed = true;
            $test->assertInstanceOf(ContainerInterface::class, $container);
        });

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

    public function testClosureJobSerializesCorrectly()
    {
        $job = new CallQueuedClosure(function () {
            return 'test';
        });

        $serialized = serialize($job);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(CallQueuedClosure::class, $unserialized);
        $this->assertEquals($job->class, $unserialized->class);
    }

    public function testClosureJobCanCaptureVariables()
    {
        $captured = 'captured value';
        $result = null;

        $job = new CallQueuedClosure(function () use ($captured, &$result) {
            $result = $captured;
        });

        // Mock the container
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertEquals('captured value', $result);
    }

    public function testClosureJobMaxAttemptsDefaultsToZero()
    {
        $job = new CallQueuedClosure(function () {
            return 'test';
        });

        $this->assertEquals(0, $job->getMaxAttempts());
    }

    public function testClosureJobWithNullableParameter()
    {
        $value = 'not null';

        $job = new CallQueuedClosure(function (?string $param = null) use (&$value) {
            $value = $param ?? 'was null';
        });

        // Mock the container with nullable parameter support
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(true);

        $definitionCollector = m::mock(ClosureDefinitionCollectorInterface::class);
        $definition = m::mock();
        $definition->shouldReceive('getMeta')
            ->with('name')
            ->andReturn('param');
        $definition->shouldReceive('getMeta')
            ->with('defaultValueAvailable')
            ->andReturn(true);
        $definition->shouldReceive('getMeta')
            ->with('defaultValue')
            ->andReturn(null);

        $definitionCollector->shouldReceive('getParameters')
            ->andReturn([0 => $definition]);

        $container->shouldReceive('get')
            ->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn($definitionCollector);

        ApplicationContext::setContainer($container);

        $job->handle();

        $this->assertEquals('was null', $value);
    }
}
