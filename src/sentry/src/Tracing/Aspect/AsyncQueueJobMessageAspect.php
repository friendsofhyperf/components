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
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
use Hyperf\AsyncQueue\Driver\RedisDriver;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Throwable;

use function Hyperf\Support\with;

/**
 * @property \Hyperf\AsyncQueue\Driver\ChannelConfig $channel
 * @property \Hyperf\Redis\RedisProxy $redis
 * @property string $poolName
 */
class AsyncQueueJobMessageAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        'Hyperf\AsyncQueue\Driver\*Driver::push',
        'Hyperf\AsyncQueue\JobMessage::__serialize',
        'Hyperf\AsyncQueue\JobMessage::__unserialize',
    ];

    public function __construct(
        protected Switcher $switcher,
        protected CarrierPacker $packer
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('async_queue')) {
            return $proceedingJoinPoint->process();
        }

        return match ($proceedingJoinPoint->methodName) {
            'push' => $this->handlePush($proceedingJoinPoint),
            '__serialize' => $this->handleSerialize($proceedingJoinPoint),
            '__unserialize' => $this->handleUnserialize($proceedingJoinPoint),
            default => $proceedingJoinPoint->process()
        };
    }

    public function handlePush(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $span = $this->startSpan(
            'queue.publish',
            $proceedingJoinPoint->arguments['keys']['job']::class
        );

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        try {
            $data = [
                'messaging.system' => 'async_queue',
            ];

            /** @var \Hyperf\AsyncQueue\Driver\Driver $driver */
            $driver = $proceedingJoinPoint->getInstance();
            $data += match (true) {
                $driver instanceof RedisDriver => $this->buildSpanDataOfRedisDriver($driver),
                default => []
            };

            $span->setData($data);
            $carrier = $this->packer->pack($span);
            Context::set(Constants::TRACE_CARRIER, $carrier);

            return $proceedingJoinPoint->process();
        } catch (Throwable) {
        } finally {
            $span->finish();
        }
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
                if (array_is_list($result)) {
                    $result[] = $carrier;
                } elseif (isset($result['job'])) {
                    $result[Constants::TRACE_CARRIER] = $carrier;
                }
            }

            return $result;
        });
    }

    protected function handleUnserialize(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var array $data */
        $data = $proceedingJoinPoint->arguments['keys']['data'] ?? [];
        $carrier = null;

        if (is_array($data)) {
            if (array_is_list($data)) {
                $carrier = end($data);
            } elseif (isset($data['job'])) {
                $carrier = $data[Constants::TRACE_CARRIER] ?? '';
            }
        }

        /** @var string|null $carrier */
        if ($carrier) {
            Context::set(Constants::TRACE_CARRIER, $carrier);
        }

        return $proceedingJoinPoint->process();
    }
}
