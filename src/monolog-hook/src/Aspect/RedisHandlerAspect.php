<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\MonologHook\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Monolog\Handler\RedisHandler;
use Psr\Container\ContainerInterface;

class RedisHandlerAspect extends AbstractAspect
{
    public $classes = [
        RedisHandler::class . '::__construct',
    ];

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $proceedingJoinPoint->arguments['keys']['redis'] = new \Redis();  // 声东击西
        $container = $this->container;

        return tap($proceedingJoinPoint->process(), function () use ($proceedingJoinPoint, $container) {
            (function () use ($container) {
                return $this->redisClient == $container->get(Redis::class); // 狸猫换太子
            })->call($proceedingJoinPoint->getInstance());
        });
    }
}
