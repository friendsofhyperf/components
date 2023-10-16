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

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\RedisConnection;
use Sentry\Breadcrumb;

use function Hyperf\Tappable\tap;

class RedisAspect extends AbstractAspect
{
    public array $classes = [
        RedisConnection::class . '::__call',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $startTime = microtime(true);

        return tap($proceedingJoinPoint->process(), function ($result) use ($arguments, $startTime) {
            if (! $this->switcher->isBreadcrumbEnable('redis')) {
                return;
            }

            $data['result'] = $result;
            $data['arguments'] = $arguments['arguments'];
            $data['timeMs'] = (microtime(true) - $startTime) * 1000;

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'redis',
                $arguments['name'],
                $data
            ));
        });
    }
}
