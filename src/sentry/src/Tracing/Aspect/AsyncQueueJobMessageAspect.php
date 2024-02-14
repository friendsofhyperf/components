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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Throwable;

use function Hyperf\Support\with;

/**
 * @property \Hyperf\AsyncQueue\Driver\ChannelConfig|null $channel
 * @property \Hyperf\Redis\RedisProxy|null $redis
 * @property string|null $poolName
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
        protected TagManager $tagManager,
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
        try {
            /** @var \Hyperf\AsyncQueue\Driver\Driver $driver */
            $driver = $proceedingJoinPoint->getInstance();
            if (! $driver instanceof \Hyperf\AsyncQueue\Driver\RedisDriver) {
                return $proceedingJoinPoint->process();
            }

            $span = $this->startSpan(
                'async_queue.job.push',
                $proceedingJoinPoint->arguments['keys']['job']::class
            );
            $data = [];

            /** @var \Hyperf\AsyncQueue\Driver\ChannelConfig|null $channelConfig */
            $channelConfig = (fn () => $this->channel ?? null)->call($driver);
            /** @var string|null $channel */
            $channel = $channelConfig?->getChannel();
            if ($channel && $this->tagManager->has('async_queue.channel')) {
                $data[$this->tagManager->get('async_queue.channel')] = $channel;
            }

            /** @var \Hyperf\Redis\RedisProxy|null $redis */
            $redis = (fn () => $this->redis ?? null)->call($driver);
            /** @var string|null $poolName */
            $poolName = (fn () => $this->poolName ?? null)->call($redis);

            if ($poolName && $this->tagManager->has('async_queue.redis_pool')) {
                $data[$this->tagManager->get('async_queue.redis_pool')] = $poolName;
            }
            if (count($data)) {
                $span->setData($data);
            }

            $carrier = $this->packer->pack($span);
            Context::set(Constants::TRACE_CARRIER, $carrier);

            return $proceedingJoinPoint->process();
        } catch (Throwable) {
        } finally {
            $span?->finish();
        }
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
        $data = $proceedingJoinPoint->arguments['keys']['data'] ?? [];
        $carrier = '';

        if (is_array($data)) {
            if (array_is_list($data)) {
                $carrier = end($data);
            } elseif (isset($data['job'])) {
                $carrier = $data[Constants::TRACE_CARRIER] ?? '';
            }
        }

        if ($carrier) {
            Context::set(Constants::TRACE_CARRIER, $carrier);
        }

        return $proceedingJoinPoint->process();
    }
}
