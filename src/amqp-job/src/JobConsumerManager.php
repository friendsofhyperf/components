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

class JobConsumerManager
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function run(): void
    {
        /** @var array<class-string<JobConsumer>,AmqpJobAnnotation> $classes */
        $classes = AnnotationCollector::getClassesByAnnotation(AmqpJobAnnotation::class);

        foreach ($classes as $class => $annotation) {
            $instance = new class extends JobConsumer {};
            $instance->setContainer($this->container);

            $annotation->exchange && $instance->setExchange($annotation->exchange);
            $annotation->routingKey && $instance->setRoutingKey($annotation->routingKey);
            $annotation->queue && $instance->setQueue($annotation->queue);
            $annotation->pool && $instance->setPoolName($annotation->pool);
            $annotation->maxConsumption && $instance->setMaxConsumption($annotation->maxConsumption);
            $annotation->consumerProcessNums && $instance->setNums($annotation->consumerProcessNums);

            if (! $annotation->autoRegisterConsumer) {
                continue;
            }

            $process = $this->createProcess($instance);
            $process->nums = $instance->getNums();
            $process->name = $class . '-consumer-' . ($instance->getQueue() ?: '[auto]');

            ProcessManager::register($process);
        }
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
