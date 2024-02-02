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
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use longlang\phpkafka\Protocol\RecordBatch\RecordHeader;
use PhpAmqpLib\Wire\AMQPTable;

use function Hyperf\Tappable\tap;

/**
 * @property array{application_headers:?AMQPTable} $properties
 */
class AmqpProducerAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Amqp\Producer::produceMessage',
    ];

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
            'amqp.produce',
            sprintf('%s::%s', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName)
        );
        $carrier = json_encode([
            'sentry-trace' => $span->toTraceparent(),
            'baggage' => $span->toBaggage(),
            'traceparent' => $span->toW3CTraceparent(),
        ]);
        $headers[] = (new RecordHeader())
            ->setHeaderKey(Constants::TRACE_CARRIER)
            ->setValue($carrier);
        (function () use ($carrier) {
            $this->properties['application_headers'] ??= new AMQPTable();
            $this->properties['application_headers']->set(Constants::TRACE_CARRIER, $carrier);
        })->call($producerMessage);

        return tap($proceedingJoinPoint->process(), fn () => $span->finish());
    }
}
