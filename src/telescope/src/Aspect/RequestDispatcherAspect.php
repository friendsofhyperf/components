<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Aspect;

use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\RpcServer\RequestDispatcher;

use function Hyperf\Tappable\tap;

class RequestDispatcherAspect extends AbstractAspect
{
    public array $classes = [
        HttpDispatcher::class . '::dispatch',
        RequestDispatcher::class . '::dispatch',
    ];

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function () use ($proceedingJoinPoint) {
            if (
                ! $this->telescopeConfig->isEnable('request')
                || ! TelescopeContext::getBatchId()
            ) {
                return;
            }

            $middlewares = $proceedingJoinPoint->arguments['keys']['params'][1] ?? null;
            $middlewares && TelescopeContext::setMiddlewares($middlewares);
        });
    }
}
