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

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RpcMultiplex\Constant;
use Multiplex\Socket\Client;

class MultiplexClientAspect extends AbstractAspect
{
    public array $classes = [
        Client::class . '::send',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var Client $instance */
        $instance = $proceedingJoinPoint->getInstance();
        $arguments = $proceedingJoinPoint->arguments['keys'];
        if (isset($arguments['data']) && is_string($arguments['data'])) {
            $data = json_decode($arguments['data'], true);
            if (! isset($data[Constant::HOST]) && is_callable([$instance, $method = 'getName'])) {
                $data[Constant::HOST] = $instance->{$method}();
                $proceedingJoinPoint->arguments['keys']['data'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }
        return $proceedingJoinPoint->process();
    }
}
