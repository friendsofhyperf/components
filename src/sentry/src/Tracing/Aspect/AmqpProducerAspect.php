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
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Util\Carrier;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use PhpAmqpLib\Wire\AMQPTable;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Util\SentryUid;

use function FriendsOfHyperf\Sentry\trace;

/**
 * @property array{application_headers?:AMQPTable} $properties
 */
class AmqpProducerAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Amqp\Producer::produceMessage',
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingEnabled('amqp')) {
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

        $messageId = SentryUid::generate();
        $destinationName = implode(', ', (array) $routingKey);
        $bodySize = strlen($producerMessage->payload());

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $producerMessage, $messageId, $destinationName, $bodySize) {
                if ($span = $scope->getSpan()) {
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
                }

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('queue.publish')
                ->setDescription(sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName))
                ->setOrigin('auto.amqp')
                ->setData([
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
                ])
        );
    }
}
