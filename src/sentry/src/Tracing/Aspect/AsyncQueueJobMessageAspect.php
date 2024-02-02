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
use Hyperf\AsyncQueue\JobMessage;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Collection\head;
use function Hyperf\Support\with;

class AsyncQueueJobMessageAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        JobMessage::class . '::__serialize',
        JobMessage::class . '::__unserialize',
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
            '__serialize' => $this->handleSerialize($proceedingJoinPoint),
            '__unserialize' => $this->handleUnserialize($proceedingJoinPoint),
            default => $proceedingJoinPoint->process()
        };
    }

    protected function handleSerialize(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return with($proceedingJoinPoint->process(), function ($result) {
            if (is_array($result)) {
                $job = array_is_list($result) ? head($result) : $result['job'] ?? null;
                $span = $this->startSpan('async_queue.job.dispatch', $job ? $job::class : null);
                $carrier = $this->packer->pack($span);
                if (array_is_list($result)) {
                    $result[] = $carrier;
                } elseif (isset($result['job'])) {
                    $result[Constants::TRACE_CARRIER] = $carrier;
                }
                $span?->finish();
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
