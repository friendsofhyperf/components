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
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
use Hyperf\Amqp\Message\ProducerMessage;
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

    public function __construct(protected CarrierPacker $packer)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->methodName) {
            'produceMessage' => $this->produceMessage($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    protected function produceMessage(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var ProducerMessage|null $producerMessage */
        $producerMessage = $proceedingJoinPoint->arguments['keys']['producerMessage'] ?? null;

        if (! $producerMessage) {
            return $proceedingJoinPoint->process();
        }

        $span = $this->startSpan(
            'topic.send',
            sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName)
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $messageId = uniqid('amqp_', true);
        $poolName = $producerMessage->getPoolName() ?: 'default';
        $bodySize = strlen($producerMessage->payload());
        $span->setData([
            'messaging.system' => 'amqp',
            'messaging.operation' => 'publish',
            'messaging.message.id' => $messageId,
            'messaging.message.body.size' => $bodySize,
            'messaging.destination.name' => $poolName,
            // for amqp
            'messaging.amqp.message.type' => $producerMessage->getTypeString(),
            'messaging.amqp.message.routing_key' => $producerMessage->getRoutingKey(),
            'messaging.amqp.message.exchange' => $producerMessage->getExchange(),
            'messaging.amqp.message.pool_name' => $poolName,
        ]);

        $carrier = $this->packer->pack($span, ['publish_time' => microtime(true),
            'message_id' => $messageId,
            'queue_name' => $poolName,
            'body_size' => $bodySize,
        ]);

        (function () use ($carrier) {
            $this->properties['application_headers'] ??= new AMQPTable();
            $this->properties['application_headers']->set(Constants::TRACE_CARRIER, $carrier);
        })->call($producerMessage);

        return tap($proceedingJoinPoint->process(), fn () => $span->setOrigin('auto.amqp')->finish());
    }
}
