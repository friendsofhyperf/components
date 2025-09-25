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

use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Coordinator\Coordinator;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

class CoordinatorAspect extends AbstractAspect
{
    public array $classes = [
        Coordinator::class . '::yield',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnabled('coordinator')) {
            return $proceedingJoinPoint->process();
        }

        $timeout = $proceedingJoinPoint->arguments['keys']['timeout'] ?? -1;

        return trace(
            fn (Scope $scope) => $proceedingJoinPoint->process(),
            SpanContext::make()
                ->setOp('coordinator.yield')
                ->setDescription(sprintf('%s::%s(%s)', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $timeout))
                ->setOrigin('auto.coordinator')
                ->setData([
                    'timeout' => $timeout,
                ])
        );
    }
}
