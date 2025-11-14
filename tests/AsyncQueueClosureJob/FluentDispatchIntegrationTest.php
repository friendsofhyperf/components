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

use Exception;
use FriendsOfHyperf\AsyncQueueClosureJob\CallQueuedClosure;
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\AsyncQueue\Driver\Driver;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;
use Mockery as m;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use stdClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('async-queue-closure-job')]
class FluentDispatchIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up proper container mocking to prevent DirectoryNotFoundException
        $this->setupDefaultContainerMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testCompleteFluentDispatchWorkflow()
    {
        $executionOrder = [];

        // Mock the container and driver factory
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $container->shouldReceive('has')
            ->with(\Hyperf\Di\ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        $driverFactory->shouldReceive('get')
            ->with('integration-connection')
            ->once()
            ->andReturn($driver);

        // Capture the job that gets pushed to verify it can be executed
        $driver->shouldReceive('push')
            ->once()
            ->with(m::type(CallQueuedClosure::class), 90)
            ->andReturnUsing(function ($job) use (&$executionOrder) {
                $executionOrder[] = 'job_pushed';
                // Simulate what would happen when the job is processed
                $this->simulateJobExecution($job, $executionOrder);
                return true;
            });

        ApplicationContext::setContainer($container);

        // Create a closure that tracks its execution
        $testValue = null;
        $closure = function () use (&$testValue, &$executionOrder) {
            $executionOrder[] = 'closure_executed';
            $testValue = 'integration test success';
            return $testValue;
        };

        $executionOrder[] = 'dispatch_started';

        // Use the fluent dispatch API
        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onPool('integration-connection')
            ->delay(90)
            ->setMaxAttempts(3);

        $executionOrder[] = 'dispatch_configured';

        // Verify the job configuration
        $job = $this->getProperty($dispatch, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
        $this->assertEquals(3, $job->getMaxAttempts());

        // Trigger destruct to simulate the job being pushed to queue
        unset($dispatch);
        $executionOrder[] = 'dispatch_completed';

        // Verify the execution order
        $expectedOrder = [
            'dispatch_started',
            'dispatch_configured',
            'job_pushed',
            'closure_executed',
            'dispatch_completed',
        ];

        $this->assertEquals($expectedOrder, $executionOrder);
        $this->assertEquals('integration test success', $testValue);
    }

    public function testFluentDispatchWithDependencyInjection()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('di-connection')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->once()
            ->andReturnUsing(function ($job) {
                $this->simulateJobExecutionWithDI($job);
                return true;
            });

        ApplicationContext::setContainer($container);

        $injectedService = new stdClass();
        $injectedService->value = 'injected service';

        $result = null;
        $closure = function (stdClass $service) use (&$result) {
            $result = $service->value;
        };

        // Set up the container and driver factory for this test
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        // Set up the container to handle dependency injection and driver factory
        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->zeroOrMoreTimes()
            ->andReturn($driverFactory);

        $container->shouldReceive('has')
            ->with(stdClass::class)
            ->andReturn(true);

        $container->shouldReceive('get')
            ->with(stdClass::class)
            ->andReturn($injectedService);

        // Add missing expectations for the destructor call
        $container->shouldReceive('has')
            ->with(\Hyperf\Di\ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);

        $driverFactory->shouldReceive('get')
            ->with('di-connection')
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->andReturnUsing(function ($job) use (&$result) {
                $this->simulateJobExecutionWithDI($job);
                return true;
            });

        // Dispatch with dependency injection
        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onPool('di-connection')
            ->delay(60)
            ->setMaxAttempts(1);

        unset($dispatch); // Trigger execution

        $this->assertEquals('injected service', $result);
    }

    public function testFluentDispatchWithConditionalLogic()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $highPriorityDriver = m::mock(Driver::class);
        $lowPriorityDriver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->zeroOrMoreTimes()
            ->andReturn($driverFactory);

        // Test with condition = true (should use high priority)
        $driverFactory->shouldReceive('get')
            ->with('high-priority')
            ->once()
            ->andReturn($highPriorityDriver);

        $highPriorityDriver->shouldReceive('push')
            ->once()
            ->andReturnTrue();

        ApplicationContext::setContainer($container);

        $executed = false;
        $condition = true;

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch(function () use (&$executed) {
            $executed = true;
        })
            ->when($condition, function ($dispatch) {
                $dispatch->onPool('high-priority');
            })
            ->unless(! $condition, function ($dispatch) {
                $dispatch->delay(30);
            })
            ->setMaxAttempts(2);

        $this->assertEquals('high-priority', $this->getProperty($dispatch, 'pool'));
        $this->assertEquals(30, $this->getProperty($dispatch, 'delay'));

        unset($dispatch);
    }

    public function testFluentDispatchWithMultipleConfigurations()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('multi-config-connection')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->once()
            ->with(m::type(CallQueuedClosure::class), 300)
            ->andReturnUsing(function ($job) {
                // Verify the job has the correct maxAttempts
                $this->assertEquals(10, $job->getMaxAttempts());
                return true;
            });

        ApplicationContext::setContainer($container);

        $closure = function () {
            return 'multi-config test';
        };

        // Test multiple method calls and overrides
        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onPool('initial-connection')
            ->delay(60)
            ->setMaxAttempts(8) // Override maxAttempts
            ->onPool('multi-config-connection') // Override queue
            ->delay(300) // Override delay
            ->setMaxAttempts(10); // Final maxAttempts

        $this->assertEquals('multi-config-connection', $this->getProperty($dispatch, 'pool'));
        $this->assertEquals(300, $this->getProperty($dispatch, 'delay'));

        $job = $this->getProperty($dispatch, 'job');
        $this->assertEquals(10, $job->getMaxAttempts());

        // Avoid triggering destructor which would try to dispatch the job
        // Instead, just verify the configuration
    }

    public function testFluentDispatchErrorHandling()
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('error-connection')
            ->once()
            ->andThrow(new Exception('Driver not found'));

        ApplicationContext::setContainer($container);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Driver not found');

        $closure = function () {
            return 'error test';
        };

        $dispatch = \FriendsOfHyperf\AsyncQueueClosureJob\dispatch($closure)
            ->onPool('error-connection')
            ->setMaxAttempts(1);

        unset($dispatch); // This should trigger the exception
    }

    protected function setupDefaultContainerMock(): void
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->zeroOrMoreTimes()
            ->andReturn($driverFactory);

        $container->shouldReceive('has')
            ->with(\Hyperf\Di\ClosureDefinitionCollectorInterface::class)
            ->andReturn(false)
            ->byDefault();

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

    /**
     * Simulate job execution for testing.
     * @param mixed $job
     */
    protected function simulateJobExecution($job, array &$executionOrder): void
    {
        // The container should already be set up with proper mocks
        // Just execute the job
        $job->handle();
    }

    /**
     * Simulate job execution with dependency injection.
     * @param mixed $job
     */
    protected function simulateJobExecutionWithDI($job): void
    {
        // Set up container expectations for dependency injection
        $container = m::mock(ContainerInterface::class);

        // Mock the ClosureDefinitionCollectorInterface
        $definitionCollector = m::mock(\Hyperf\Di\ClosureDefinitionCollectorInterface::class);
        $definition = m::mock(\Hyperf\Di\Definition\DefinitionInterface::class);

        $container->shouldReceive('has')
            ->with(\Hyperf\Di\ClosureDefinitionCollectorInterface::class)
            ->andReturn(true);

        $container->shouldReceive('get')
            ->with(\Hyperf\Di\ClosureDefinitionCollectorInterface::class)
            ->andReturn($definitionCollector);

        // Mock the definition for stdClass parameter
        $definitionCollector->shouldReceive('getParameters')
            ->withAnyArgs()
            ->andReturn([$definition]);

        $definition->shouldReceive('getMeta')
            ->with('name')
            ->andReturn('service');

        $definition->shouldReceive('getName')
            ->andReturn(stdClass::class);

        $definition->shouldReceive('getMeta')
            ->with('defaultValueAvailable')
            ->andReturn(false);

        $definition->shouldReceive('allowsNull')
            ->andReturn(false);

        // Set up dependency injection for stdClass
        $container->shouldReceive('has')
            ->with(stdClass::class)
            ->andReturn(true);

        $injectedService = new stdClass();
        $injectedService->value = 'injected service';

        $container->shouldReceive('get')
            ->with(stdClass::class)
            ->andReturn($injectedService);

        ApplicationContext::setContainer($container);

        // Execute the job
        $job->handle();
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
