<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Aspect;

use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class SingletonAspect extends AbstractAspect
{
    public array $classes = [
        \Sentry\CheckInStatus::class . '::getInstance',
        \Sentry\EventType::class . '::getInstance',
        \Sentry\MonitorScheduleUnit::class . '::getInstance',
        \Sentry\Integration\IntegrationRegistry::class . '::getInstance',
        \Sentry\Logs\LogLevel::class . '::getInstance',
        \Sentry\Metrics\TraceMetrics::class . '::getInstance',
        \Sentry\State\HubAdapter::class . '::getInstance',
        \Sentry\Tracing\SpanStatus::class . '::getInstance',
        \Sentry\Tracing\TransactionSource::class . '::getInstance',
        \Sentry\Transport\ResultStatus::class . '::getInstance',
        \Sentry\Unit::class . '::getInstance',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $key = $proceedingJoinPoint->className;
        $args = $proceedingJoinPoint->getArguments();

        if (! empty($args)) {
            $key .= '#' . $args[0];
        }

        return Context::getOrSet($key, fn () => $proceedingJoinPoint->process());
    }
}
