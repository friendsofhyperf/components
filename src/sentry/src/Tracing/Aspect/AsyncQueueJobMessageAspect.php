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
use Hyperf\AsyncQueue\Driver\RedisDriver;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Util\SentryUid;

use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Support\with;

/**
 * @property \Hyperf\AsyncQueue\Driver\ChannelConfig $channel
 * @property \Hyperf\Redis\RedisProxy $redis
 * @property \Hyperf\Contract\PackerInterface $packer
 * @property string $poolName
 */
class AsyncQueueJobMessageAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\AsyncQueue\Driver\DriverFactory::get',
        'Hyperf\AsyncQueue\Driver\*Driver::push',
        'Hyperf\AsyncQueue\JobMessage::__serialize',
        'Hyperf\AsyncQueue\JobMessage::__unserialize',
    ];

    public function __construct(
        protected Feature $feature
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingEnabled('async_queue')) {
            return $proceedingJoinPoint->process();
        }

        return match ($proceedingJoinPoint->methodName) {
            'get' => $this->handleGet($proceedingJoinPoint),
            'push' => $this->handlePush($proceedingJoinPoint),
            '__serialize' => $this->handleSerialize($proceedingJoinPoint),
            '__unserialize' => $this->handleUnserialize($proceedingJoinPoint),
            default => $proceedingJoinPoint->process()
        };
    }

    public function handleGet(ProceedingJoinPoint $proceedingJoinPoint)
    {
        Context::set(
            'sentry.messaging.destination.name',
            $proceedingJoinPoint->arguments['keys']['name'] ?? 'default'
        );

        return $proceedingJoinPoint->process();
    }

    public function handlePush(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var \Hyperf\AsyncQueue\JobInterface $job */
        $job = $proceedingJoinPoint->arguments['keys']['job'] ?? null;

        /** @var \Hyperf\AsyncQueue\Driver\Driver $driver */
        $driver = $proceedingJoinPoint->getInstance();
        $messageId = method_exists($job, 'getId') ? $job->getId() : SentryUid::generate();
        $destinationName = Context::get('sentry.messaging.destination.name', 'default');
        $bodySize = (fn ($job) => strlen($this->packer->pack($job)))->call($driver, $job);
        $data = [
            'messaging.system' => 'async_queue',
            'messaging.operation' => 'publish',
            'messaging.message.id' => $messageId,
            'messaging.message.body.size' => $bodySize,
            'messaging.destination.name' => $destinationName,
        ];

        if ($driver instanceof RedisDriver) {
            $data = array_merge($data, $this->buildSpanDataOfRedisDriver($driver));
        }

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint, $messageId, $destinationName, $bodySize) {
                $extra = [
                    'publish_time' => microtime(true),
                    'message_id' => $messageId,
                    'destination_name' => $destinationName,
                    'body_size' => $bodySize,
                ];
                if ($span = $scope->getSpan()) {
                    $carrier = Carrier::fromSpan($span)->with($extra);
                } else {
                    $carrier = Carrier::fromArray([])->with($extra);
                }

                Context::set(Constants::TRACE_CARRIER, $carrier);

                return $proceedingJoinPoint->process();
            },
            SpanContext::make()
                ->setOp('queue.publish')
                ->setDescription($job::class)
                ->setOrigin('auto.queue')
                ->setData($data)
        );
    }

    protected function buildSpanDataOfRedisDriver(RedisDriver $driver): array
    {
        $data = [];

        /** @var \Hyperf\AsyncQueue\Driver\ChannelConfig $channelConfig */
        $channelConfig = (fn () => $this->channel)->call($driver);
        /** @var string $channel */
        $channel = $channelConfig->getChannel();
        $data['async_queue.channel'] = $channel;

        /** @var \Hyperf\Redis\RedisProxy $redis */
        $redis = (fn () => $this->redis)->call($driver);
        /** @var string $poolName */
        $poolName = (fn () => $this->poolName ?? 'default')->call($redis);
        $data['async_queue.redis_pool'] = $poolName;

        return $data;
    }

    protected function handleSerialize(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return with($proceedingJoinPoint->process(), function ($result) {
            if (is_array($result) && $carrier = Context::get(Constants::TRACE_CARRIER)) {
                $result[Constants::TRACE_CARRIER] = $carrier->toJson();
            }

            return $result;
        });
    }

    protected function handleUnserialize(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var array $data */
        $data = $proceedingJoinPoint->arguments['keys']['data'] ?? [];
        $carrier = $data['job'] ?? null;

        /** @var null|string $carrier */
        if ($carrier) {
            Context::set(Constants::TRACE_CARRIER, Carrier::fromJson($carrier));
        }

        return $proceedingJoinPoint->process();
    }
}
