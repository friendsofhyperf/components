<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Helpers;

use Exception;
use FriendsOfHyperf\AsyncQueueClosureJob\CallQueuedClosure;
use FriendsOfHyperf\Helpers\PendingAmqpProducerMessageDispatch;
use FriendsOfHyperf\Helpers\PendingAsyncQueueDispatch;
use FriendsOfHyperf\Helpers\PendingKafkaProducerMessageDispatch;
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Producer;
use Hyperf\AsyncQueue\Driver\Driver;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Kafka\Producer as HyperfKafkaProducer;
use Hyperf\Kafka\ProducerManager;
use InvalidArgumentException;
use longlang\phpkafka\Producer\ProduceMessage;
use Mockery as m;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use stdClass;

use function FriendsOfHyperf\Helpers\dispatch;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('helpers')]
class DispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupDefaultContainerMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        m::close();
    }

    public function testDispatchWithClosure()
    {
        $closure = function () {
            return 'test';
        };

        $result = dispatch($closure);

        $this->assertInstanceOf(PendingAsyncQueueDispatch::class, $result);

        // Verify the underlying job is CallQueuedClosure
        $job = $this->getProperty($result, 'job');
        $this->assertInstanceOf(CallQueuedClosure::class, $job);
    }

    public function testDispatchWithJobInterface()
    {
        $job = m::mock(JobInterface::class);

        $result = dispatch($job);

        $this->assertInstanceOf(PendingAsyncQueueDispatch::class, $result);
        $this->assertSame($job, $this->getProperty($result, 'job'));
    }

    public function testDispatchWithProducerMessage()
    {
        $message = m::mock(ProducerMessage::class);

        $result = dispatch($message);

        $this->assertInstanceOf(PendingAmqpProducerMessageDispatch::class, $result);
        $this->assertSame($message, $this->getProperty($result, 'message'));
    }

    public function testDispatchWithKafkaProduceMessage()
    {
        $message = new ProduceMessage('test-topic', 'test-value');

        $result = dispatch($message);

        $this->assertInstanceOf(PendingKafkaProducerMessageDispatch::class, $result);
        $this->assertSame($message, $this->getProperty($result, 'message'));
    }

    public function testDispatchWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported job type.');

        dispatch(new stdClass());
    }

    public function testPendingAsyncQueueDispatchOnPool()
    {
        $job = m::mock(JobInterface::class);
        $pending = dispatch($job);

        $result = $pending->onPool('custom-pool');

        $this->assertSame($pending, $result);
        $this->assertEquals('custom-pool', $this->getProperty($pending, 'pool'));
    }

    public function testPendingAsyncQueueDispatchDelay()
    {
        $job = m::mock(JobInterface::class);
        $pending = dispatch($job);

        $result = $pending->delay(60);

        $this->assertSame($pending, $result);
        $this->assertEquals(60, $this->getProperty($pending, 'delay'));
    }

    public function testPendingAsyncQueueDispatchSetMaxAttempts()
    {
        $job = m::mock(JobInterface::class);
        $job->shouldReceive('setMaxAttempts')
            ->with(5)
            ->once()
            ->andReturnSelf();

        $pending = dispatch($job);

        $result = $pending->setMaxAttempts(5);

        $this->assertSame($pending, $result);
    }

    public function testPendingAsyncQueueDispatchFluentChaining()
    {
        $job = m::mock(JobInterface::class);
        $job->shouldReceive('setMaxAttempts')
            ->with(3)
            ->once()
            ->andReturnSelf();

        $pending = dispatch($job)
            ->onPool('high-priority')
            ->delay(30)
            ->setMaxAttempts(3);

        $this->assertEquals('high-priority', $this->getProperty($pending, 'pool'));
        $this->assertEquals(30, $this->getProperty($pending, 'delay'));
    }

    public function testPendingAsyncQueueDispatchExecutesOnDestruct()
    {
        $job = m::mock(JobInterface::class);
        $pushed = false;

        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('test-pool')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->with($job, 10)
            ->once()
            ->andReturnUsing(function () use (&$pushed) {
                $pushed = true;
                return true;
            });

        ApplicationContext::setContainer($container);

        $pending = dispatch($job)
            ->onPool('test-pool')
            ->delay(10);

        // Trigger destruct
        unset($pending);

        $this->assertTrue($pushed, 'Job should have been pushed to queue');
    }

    public function testPendingAsyncQueueDispatchWithConditionable()
    {
        $job = m::mock(JobInterface::class);

        $pending = dispatch($job)
            ->when(true, function ($dispatch) {
                $dispatch->onPool('conditional-pool');
            })
            ->unless(false, function ($dispatch) {
                $dispatch->delay(20);
            });

        $this->assertEquals('conditional-pool', $this->getProperty($pending, 'pool'));
        $this->assertEquals(20, $this->getProperty($pending, 'delay'));
    }

    public function testPendingAmqpProducerMessageDispatchOnPool()
    {
        $message = m::mock(ProducerMessage::class);
        $pending = dispatch($message);

        $result = $pending->onPool('amqp-custom');

        $this->assertSame($pending, $result);
        $this->assertEquals('amqp-custom', $this->getProperty($pending, 'pool'));
    }

    public function testPendingAmqpProducerMessageDispatchSetConfirm()
    {
        $message = m::mock(ProducerMessage::class);
        $pending = dispatch($message);

        $result = $pending->setConfirm(true);

        $this->assertSame($pending, $result);
        $this->assertTrue($this->getProperty($pending, 'confirm'));
    }

    public function testPendingAmqpProducerMessageDispatchSetTimeout()
    {
        $message = m::mock(ProducerMessage::class);
        $pending = dispatch($message);

        $result = $pending->setTimeout(10);

        $this->assertSame($pending, $result);
        $this->assertEquals(10, $this->getProperty($pending, 'timeout'));
    }

    public function testPendingAmqpProducerMessageDispatchFluentChaining()
    {
        $message = m::mock(ProducerMessage::class);

        $pending = dispatch($message)
            ->onPool('amqp-pool')
            ->setConfirm(true)
            ->setTimeout(15);

        $this->assertEquals('amqp-pool', $this->getProperty($pending, 'pool'));
        $this->assertTrue($this->getProperty($pending, 'confirm'));
        $this->assertEquals(15, $this->getProperty($pending, 'timeout'));
    }

    public function testPendingAmqpProducerMessageDispatchExecutesOnDestruct()
    {
        $message = m::mock(ProducerMessage::class);
        $produced = false;

        $container = m::mock(ContainerInterface::class);
        $producer = m::mock(Producer::class);

        $container->shouldReceive('get')
            ->with(Producer::class)
            ->once()
            ->andReturn($producer);

        $producer->shouldReceive('produce')
            ->with($message, true, 10)
            ->once()
            ->andReturnUsing(function () use (&$produced) {
                $produced = true;
                return true;
            });

        ApplicationContext::setContainer($container);

        $pending = dispatch($message)
            ->setConfirm(true)
            ->setTimeout(10);

        // Trigger destruct
        unset($pending);

        $this->assertTrue($produced, 'Message should have been produced');
    }

    public function testPendingAmqpProducerMessageDispatchWithConditionable()
    {
        $message = m::mock(ProducerMessage::class);

        $pending = dispatch($message)
            ->when(true, function ($dispatch) {
                $dispatch->setConfirm(true);
            })
            ->unless(false, function ($dispatch) {
                $dispatch->setTimeout(20);
            });

        $this->assertTrue($this->getProperty($pending, 'confirm'));
        $this->assertEquals(20, $this->getProperty($pending, 'timeout'));
    }

    public function testPendingKafkaProducerMessageDispatchOnPool()
    {
        $message = new ProduceMessage('test-topic', 'test-value');
        $pending = dispatch($message);

        $result = $pending->onPool('kafka-custom');

        $this->assertSame($pending, $result);
        $this->assertEquals('kafka-custom', $this->getProperty($pending, 'pool'));
    }

    public function testPendingKafkaProducerMessageDispatchWithHeader()
    {
        $message = new ProduceMessage('test-topic', 'test-value');
        $pending = dispatch($message);

        $result = $pending->withHeader('trace-id', '12345');

        $this->assertSame($pending, $result);

        // Verify header was added to the message
        $headers = $this->getProperty($message, 'headers');
        $this->assertIsArray($headers);
        $this->assertNotEmpty($headers);
    }

    public function testPendingKafkaProducerMessageDispatchFluentChaining()
    {
        $message = new ProduceMessage('test-topic', 'test-value');

        $pending = dispatch($message)
            ->onPool('kafka-pool')
            ->withHeader('user-id', '123')
            ->withHeader('request-id', 'abc');

        $this->assertEquals('kafka-pool', $this->getProperty($pending, 'pool'));

        // Verify both headers were added
        $headers = $this->getProperty($message, 'headers');
        $this->assertIsArray($headers);
        $this->assertCount(2, $headers);
    }

    public function testPendingKafkaProducerMessageDispatchExecutesOnDestruct()
    {
        $message = new ProduceMessage('test-topic', 'test-value');
        $sent = false;

        $container = m::mock(ContainerInterface::class);
        $producerManager = m::mock(ProducerManager::class);
        $kafkaProducer = m::mock(HyperfKafkaProducer::class);

        $container->shouldReceive('get')
            ->with(ProducerManager::class)
            ->once()
            ->andReturn($producerManager);

        $producerManager->shouldReceive('getProducer')
            ->with('kafka-pool')
            ->once()
            ->andReturn($kafkaProducer);

        $kafkaProducer->shouldReceive('sendBatch')
            ->with([$message])
            ->once()
            ->andReturnUsing(function () use (&$sent) {
                $sent = true;
                return true;
            });

        ApplicationContext::setContainer($container);

        $pending = dispatch($message)
            ->onPool('kafka-pool');

        // Trigger destruct
        unset($pending);

        $this->assertTrue($sent, 'Message should have been sent');
    }

    public function testPendingKafkaProducerMessageDispatchWithConditionable()
    {
        $message = new ProduceMessage('test-topic', 'test-value');

        $pending = dispatch($message)
            ->when(true, function ($dispatch) {
                $dispatch->withHeader('conditional', 'true');
            })
            ->unless(false, function ($dispatch) {
                $dispatch->onPool('conditional-pool');
            });

        $this->assertEquals('conditional-pool', $this->getProperty($pending, 'pool'));

        $headers = $this->getProperty($message, 'headers');
        $this->assertNotEmpty($headers);
    }

    public function testBackwardCompatibilityWithBasicDispatch()
    {
        $job = m::mock(JobInterface::class);

        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('default')
            ->once()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->with($job, 0)
            ->once()
            ->andReturn(true);

        ApplicationContext::setContainer($container);

        // Test basic dispatch without any configuration
        $pending = dispatch($job);

        // Verify defaults
        $this->assertEquals('default', $this->getProperty($pending, 'pool'));
        $this->assertEquals(0, $this->getProperty($pending, 'delay'));

        // Trigger destruct
        unset($pending);
    }

    public function testDispatchWithErrorHandling()
    {
        $job = m::mock(JobInterface::class);

        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);

        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->once()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->with('default')
            ->once()
            ->andThrow(new Exception('Driver not found'));

        ApplicationContext::setContainer($container);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Driver not found');

        $pending = dispatch($job);

        // Trigger destruct which should throw
        unset($pending);
    }

    protected function setupDefaultContainerMock(): void
    {
        $container = m::mock(ContainerInterface::class);
        $driverFactory = m::mock(DriverFactory::class);
        $driver = m::mock(Driver::class);
        $producer = m::mock(Producer::class);
        $producerManager = m::mock(ProducerManager::class);
        $kafkaProducer = m::mock(HyperfKafkaProducer::class);

        // Setup for AsyncQueue dispatch
        $container->shouldReceive('get')
            ->with(DriverFactory::class)
            ->zeroOrMoreTimes()
            ->andReturn($driverFactory);

        $driverFactory->shouldReceive('get')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($driver);

        $driver->shouldReceive('push')
            ->zeroOrMoreTimes()
            ->andReturnTrue();

        // Setup for AMQP dispatch
        $container->shouldReceive('get')
            ->with(Producer::class)
            ->zeroOrMoreTimes()
            ->andReturn($producer);

        $producer->shouldReceive('produce')
            ->zeroOrMoreTimes()
            ->andReturnTrue();

        // Setup for Kafka dispatch
        $container->shouldReceive('get')
            ->with(ProducerManager::class)
            ->zeroOrMoreTimes()
            ->andReturn($producerManager);

        $producerManager->shouldReceive('getProducer')
            ->withAnyArgs()
            ->zeroOrMoreTimes()
            ->andReturn($kafkaProducer);

        $kafkaProducer->shouldReceive('sendBatch')
            ->zeroOrMoreTimes()
            ->andReturnTrue();

        ApplicationContext::setContainer($container);
    }

    /**
     * Helper method to get protected/private property value.
     */
    protected function getProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}
