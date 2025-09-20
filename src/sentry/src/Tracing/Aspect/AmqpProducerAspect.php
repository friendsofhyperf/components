<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Util\Carrier;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use PhpAmqpLib\Wire\AMQPTable;

use function Hyperf\Tappable\tap;

/**
 * @property array{application_headers?:AMQPTable} $properties
 */
class AmqpProducerAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Amqp\Producer::produceMessage',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnabled('amqp')) {
            return $proceedingJoinPoint->process();
        }

        return match ($proceedingJoinPoint->methodName) {
            'produceMessage' => $this->handleProduceMessage($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    protected function handleProduceMessage(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var null|ProducerMessage $producerMessage */
        $producerMessage = $proceedingJoinPoint->arguments['keys']['producerMessage'] ?? null;

        if (! $producerMessage) {
            return $proceedingJoinPoint->process();
        }

        $span = $this->startSpan(
            op: 'queue.publish',
            description: sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName),
            origin: 'auto.amqp'
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $routingKey = $producerMessage->getRoutingKey();
        $exchange = $producerMessage->getExchange();
        $poolName = $producerMessage->getPoolName();

        if (class_exists(AnnotationCollector::class)) {
            /** @var null|Producer $annotation */
            $annotation = AnnotationCollector::getClassAnnotation(get_class($producerMessage), Producer::class);
            if ($annotation) {
                $annotation->routingKey && $routingKey = $annotation->routingKey;
                $annotation->exchange && $exchange = $annotation->exchange;
                $annotation->pool && $poolName = $annotation->pool;
            }
        }

        $messageId = uniqid('amqp_', true);
        $destinationName = implode(', ', (array) $routingKey);
        $bodySize = strlen($producerMessage->payload());
        $span->setData([
            'messaging.system' => 'amqp',
            'messaging.operation' => 'publish',
            'messaging.message.id' => $messageId,
            'messaging.message.body.size' => $bodySize,
            'messaging.destination.name' => $destinationName,
            // for amqp
            'messaging.amqp.message.type' => $producerMessage->getTypeString(),
            'messaging.amqp.message.routing_key' => $routingKey,
            'messaging.amqp.message.exchange' => $exchange,
            'messaging.amqp.message.pool_name' => $poolName,
        ]);
        $carrier = Carrier::fromSpan($span)->with([
            'publish_time' => microtime(true),
            'message_id' => $messageId,
            'destination_name' => $destinationName,
            'body_size' => $bodySize,
        ]);

        (function () use ($carrier) {
            $this->properties['application_headers'] ??= new AMQPTable();
            $this->properties['application_headers']->set(Constants::TRACE_CARRIER, $carrier->toJson());
        })->call($producerMessage);

        return tap($proceedingJoinPoint->process(), fn () => $span->finish());
    }
}
