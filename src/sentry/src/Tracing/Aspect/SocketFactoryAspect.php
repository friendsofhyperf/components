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

use FriendsOfHyperf\Sentry\Util\SocketOptionContainer;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Contract\Socket\SocketOptionInterface;
use Hyperf\Engine\Contract\SocketInterface;

use function Hyperf\Tappable\tap;

class SocketFactoryAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Engine\Socket\SocketFactory::make',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($socket) use ($proceedingJoinPoint) {
            $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
            /** @var null|SocketOptionInterface $options */
            $options = $arguments['option'] ?? null;
            if ($options instanceof SocketOptionInterface && $socket instanceof SocketInterface) {
                SocketOptionContainer::set($socket, $options);
            }
        });
    }
}
