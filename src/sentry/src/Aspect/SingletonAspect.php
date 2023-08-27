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
        \Sentry\EventType::class . '::getInstance',
        \Sentry\ResponseStatus::class . '::getInstance',
        \Sentry\Integration\IntegrationRegistry::class . '::getInstance',
        \Sentry\State\HubAdapter::class . '::getInstance',
        \Sentry\Tracing\SpanStatus::class . '::getInstance',
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
