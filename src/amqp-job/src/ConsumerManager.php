<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob;

use FriendsOfHyperf\AmqpJob\Annotation\AmqpJob as AmqpJobAnnotation;
use Hyperf\Amqp\Consumer;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;

class ConsumerManager
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function run(): void
    {
        $classes = AnnotationCollector::getClassesByAnnotation(AmqpJobAnnotation::class);
        /**
         * @var string $class
         * @var AmqpJobAnnotation $annotation
         */
        foreach ($classes as $class => $annotation) {
            $instance = $this->createConsumerMessage($annotation);
            $process = $this->createProcess($instance);
            $process->nums = $annotation->nums;
            $process->name = $class . '-' . $annotation->routingKey;
            ProcessManager::register($process);
        }
    }

    private function createConsumerMessage(AmqpJobAnnotation $amqpJob): JobConsumer
    {
        return new class($this->container, $amqpJob) extends JobConsumer {
            public function __construct(ContainerInterface $container, AmqpJobAnnotation $amqpJob)
            {
                $this->routingKey = $amqpJob->routingKey;
                $this->container = $container;
                $this->nums = $amqpJob->nums;
                $this->enable = $amqpJob->enable;
                $this->exchange = $amqpJob->exchange;
                $this->poolName = $amqpJob->pool;
                $this->queue = $amqpJob->routingKey;
            }
        };
    }

    private function createProcess(ConsumerMessageInterface $consumerMessage): AbstractProcess
    {
        return new class($this->container, $consumerMessage) extends AbstractProcess {
            private Consumer $consumer;

            private ConsumerMessageInterface $consumerMessage;

            public function __construct(ContainerInterface $container, ConsumerMessageInterface $consumerMessage)
            {
                parent::__construct($container);
                $this->consumer = $container->get(Consumer::class);
                $this->consumerMessage = $consumerMessage;
            }

            public function handle(): void
            {
                $this->consumer->consume($this->consumerMessage);
            }

            public function getConsumerMessage(): ConsumerMessageInterface
            {
                return $this->consumerMessage;
            }

            public function isEnable($server): bool
            {
                return $this->consumerMessage->isEnable();
            }
        };
    }
}
