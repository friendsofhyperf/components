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
        $key = $className = $proceedingJoinPoint->className;
        $arguments = $proceedingJoinPoint->getArguments();

        if (isset($arguments[0])) {
            $key .= '#' . $arguments[0];
        }

        return Context::getOrSet($key, function () use ($proceedingJoinPoint, $className, $arguments) {
            // Reset singleton instance before proceeding
            Closure::bind(function () use ($className, $arguments) {
                if (property_exists($className, 'instance')) {
                    $className::$instance = null;
                } elseif (
                    property_exists($className, 'instances')
                    && isset($arguments[0])
                    && array_key_exists($arguments[0], $className::$instances)
                ) {
                    $className::$instances[$arguments[0]] = null;
                }
            }, null, $className)();

            // Proceed to get the singleton instance
            return $proceedingJoinPoint->process();
        });
    }
}
