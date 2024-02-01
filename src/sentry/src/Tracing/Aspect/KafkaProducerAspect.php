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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Protocol\RecordBatch\RecordHeader;

use function Hyperf\Tappable\tap;

/**
 * @property array $headers
 */
class KafkaProducerAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'longlang\phpkafka\Producer\Producer::sendBatch',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var ProduceMessage[] $messages */
        $messages = $proceedingJoinPoint->arguments['keys']['messages'] ?? [];
        $op = count($messages) > 1 ? 'kafka.producer.send_batch' : 'kafka.producer.send';
        $span = $this->startSpan($op);
        $carrier = json_encode([
            'sentry-trace' => $span->toTraceparent(),
            'baggage' => $span->toBaggage(),
            'traceparent' => $span->toW3CTraceparent(),
        ]);

        foreach ($messages as $message) {
            (
                fn () => $this->headers[] = (new RecordHeader())
                    ->setHeaderKey(Constants::JOB_CARRIER)
                    ->setValue($carrier)
            )->call($message);
        }

        return tap($proceedingJoinPoint->process(), fn () => $span->finish());
    }
}
