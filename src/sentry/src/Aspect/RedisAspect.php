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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\RedisConnection;
use Sentry\Breadcrumb;

use function Hyperf\Tappable\tap;

/**
 * @deprecated since v3.1, will be removed in v3.2.
 */
class RedisAspect extends AbstractAspect
{
    public array $classes = [
        RedisConnection::class . '::__call',
    ];

    public function __construct(protected Feature $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $startTime = microtime(true);

        return tap($proceedingJoinPoint->process(), function ($result) use ($arguments, $startTime) {
            if (
                class_exists(CommandExecuted::class)
                || ! $this->switcher->isBreadcrumbEnabled('redis')
            ) {
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
