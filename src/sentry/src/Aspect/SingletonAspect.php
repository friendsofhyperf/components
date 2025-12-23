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

use Closure;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class SingletonAspect extends AbstractAspect
{
    public array $classes = [
        // Singleton Classes
        \Sentry\State\HubAdapter::class . '::getInstance',
        \Sentry\Integration\IntegrationRegistry::class . '::getInstance',
        \Sentry\Logs\Logs::class . '::getInstance',
        \Sentry\Metrics\TraceMetrics::class . '::getInstance',
        // Enums
        // \Sentry\CheckInStatus::class . '::getInstance',
        // \Sentry\EventType::class . '::getInstance',
        // \Sentry\MonitorScheduleUnit::class . '::getInstance',
        // \Sentry\Logs\LogLevel::class . '::getInstance',
        // \Sentry\Tracing\SpanStatus::class . '::getInstance',
        // \Sentry\Tracing\TransactionSource::class . '::getInstance',
        // \Sentry\Transport\ResultStatus::class . '::getInstance',
        // \Sentry\Unit::class . '::getInstance',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $key = $className = $proceedingJoinPoint->className;
        $arguments = $proceedingJoinPoint->getArguments();

        if (! array_key_exists(0, $arguments)) {
            $key .= '#' . $arguments[0];
        }

        return match ($className) {
            // Singleton Classes
            \Sentry\State\HubAdapter::class,
            \Sentry\Integration\IntegrationRegistry::class,
            \Sentry\Logs\Logs::class => Context::getOrSet($key, function () use ($className) {
                return Closure::bind(fn () => new $className(), null, $className)();
            }),
            \Sentry\Metrics\TraceMetrics::class => Context::getOrSet($key, function () use ($className) {
                return new $className();
            }),

            // Enums
            // \Sentry\CheckInStatus::class,
            // \Sentry\EventType::class,
            // \Sentry\MonitorScheduleUnit::class,
            // \Sentry\Logs\LogLevel::class,
            // \Sentry\Tracing\SpanStatus::class,
            // \Sentry\Tracing\TransactionSource::class,
            // \Sentry\Transport\ResultStatus::class,
            // \Sentry\Unit::class => $proceedingJoinPoint->process(),
            default => $proceedingJoinPoint->process(),
        };
    }
}
