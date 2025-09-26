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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Protocol\RecordBatch\RecordHeader;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

/**
 * @property array $headers
 * @property string $name
 * @mixin ProduceMessage
 */
class KafkaProducerAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Kafka\Producer::sendAsync',
        'Hyperf\Kafka\Producer::sendBatchAsync',
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingEnabled('kafka')) {
            return $proceedingJoinPoint->process();
        }

        return match ($proceedingJoinPoint->methodName) {
            'sendAsync' => $this->sendAsync($proceedingJoinPoint),
            'sendBatchAsync' => $this->sendBatchAsync($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    protected function sendAsync(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $messageId = uniqid('kafka_', true);
        $destinationName = $proceedingJoinPoint->arguments['keys']['topic'] ?? 'unknown';
        $bodySize = strlen($proceedingJoinPoint->arguments['keys']['value'] ?? '');

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $messageId, $destinationName, $bodySize) {
                $span = $scope->getSpan();
                if ($span) {
                    $carrier = Carrier::fromSpan($span)
                        ->with([
                            'publish_time' => microtime(true),
                            'message_id' => $messageId,
                            'destination_name' => $destinationName,
                            'body_size' => $bodySize,
                        ]);
                    $headers = $proceedingJoinPoint->arguments['keys']['headers'] ?? [];
                    $headers[] = (new RecordHeader())
                        ->setHeaderKey(Constants::TRACE_CARRIER)
                        ->setValue($carrier->toJson());
                    $proceedingJoinPoint->arguments['keys']['headers'] = $headers;
                }

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('queue.publish')
                ->setDescription(sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName))
                ->setOrigin('auto.kafka')
                ->setData([
                    'messaging.system' => 'kafka',
                    'messaging.operation' => 'publish',
                    'messaging.message.id' => $messageId,
                    'messaging.message.body.size' => $bodySize,
                    'messaging.destination.name' => $destinationName,
                ])
        );
    }

    protected function sendBatchAsync(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var ProduceMessage[] $messages */
        $messages = $proceedingJoinPoint->arguments['keys']['messages'] ?? [];

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $messages) {
                $span = $scope->getSpan();

                if ($span) {
                    foreach ($messages as $message) {
                        (function () use ($span) {
                            $carrier = Carrier::fromSpan($span)
                                ->with([
                                    'publish_time' => microtime(true),
                                    'message_id' => uniqid('kafka_', true),
                                    'destination_name' => $this->getTopic(),
                                    'body_size' => strlen((string) $this->getValue()),
                                ]);
                            $this->headers[] = (new RecordHeader())->setHeaderKey(Constants::TRACE_CARRIER)->setValue($carrier->toJson());
                        })->call($message);
                    }
                }

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('queue.publish')
                ->setDescription(sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName))
                ->setOrigin('auto.kafka')
                ->setData(['message.count' => count($messages)])
        );
    }
}
