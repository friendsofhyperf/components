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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Protocol\RecordBatch\RecordHeader;

use function Hyperf\Tappable\tap;

/**
 * @property array $headers
 * @property string $name
 */
class KafkaProducerAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\Kafka\Producer::sendAsync',
        'Hyperf\Kafka\Producer::sendBatchAsync',
    ];

    public function __construct(protected CarrierPacker $packer)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->methodName) {
            'sendAsync' => $this->sendAsync($proceedingJoinPoint),
            'sendBatchAsync' => $this->sendBatchAsync($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    protected function sendAsync(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $span = $this->startSpan(
            'topic.send',
            sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName)
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $messageId = uniqid('kafka_', true);
        $poolName = (fn () => $this->name)->call($proceedingJoinPoint->getInstance());
        $bodySize = strlen($proceedingJoinPoint->arguments['keys']['value'] ?? '');

        $span->setData([
            'messaging.system' => 'kafka',
            'messaging.message.id' => $messageId,
            // 'messaging.destination.name' => $poolName,
            'messaging.message.body.size' => $bodySize,
            'messaging.operation' => 'publish',
            'messaging.destination.name' => $proceedingJoinPoint->arguments['keys']['topic'] ?? 'unknown',
        ]);

        $carrier = $this->packer->pack($span, [
            'publish_time' => microtime(true),
            'message_id' => $messageId,
            'queue_name' => $poolName,
            'body_size' => $bodySize,
        ]);
        $headers = $proceedingJoinPoint->arguments['keys']['headers'] ?? [];
        $headers[] = (new RecordHeader())
            ->setHeaderKey(Constants::TRACE_CARRIER)
            ->setValue($carrier);
        $proceedingJoinPoint->arguments['keys']['headers'] = $headers;

        return tap($proceedingJoinPoint->process(), fn () => $span->setOrigin('auto.kafka')->finish());
    }

    protected function sendBatchAsync(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var ProduceMessage[] $messages */
        $messages = $proceedingJoinPoint->arguments['keys']['messages'] ?? [];
        $span = $this->startSpan(
            'kafka.send_batch',
            sprintf('%s::%s', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName)
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $carrier = $this->packer->pack($span);

        foreach ($messages as $message) {
            (
                fn () => $this->headers[] = (new RecordHeader())
                    ->setHeaderKey(Constants::TRACE_CARRIER)
                    ->setValue($carrier)
            )->call($message);
        }

        return tap($proceedingJoinPoint->process(), fn () => $span->setOrigin('auto.kafka')->finish());
    }
}
