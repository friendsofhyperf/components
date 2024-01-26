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
use Hyperf\Stringable\Str;
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
        $data = $proceedingJoinPoint->arguments['keys']['data'] ?? null;

        if ($data && Str::isJson($data)) {
            $data = json_decode($data, true);

            if (! isset($data[Constant::HOST]) && is_callable([$instance, $method = 'getName'])) {
                $data[Constant::HOST] = $instance->{$method}();
                $proceedingJoinPoint->arguments['keys']['data'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }

        return $proceedingJoinPoint->process();
    }
}
