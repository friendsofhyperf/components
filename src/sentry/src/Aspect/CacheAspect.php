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
use Sentry\Breadcrumb;

use function Hyperf\Tappable\tap;

class CacheAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Cache\Driver\*Driver::fetch',
        'Hyperf\Cache\Driver\*Driver::get',
        'Hyperf\Cache\Driver\*Driver::getMultiple',
        'Hyperf\Cache\Driver\*Driver::set',
        'Hyperf\Cache\Driver\*Driver::setMultiple',
        'Hyperf\Cache\Driver\*Driver::delete',
        'Hyperf\Cache\Driver\*Driver::deleteMultiple',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);

        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            if (! $this->switcher->isBreadcrumbEnabled('cache')) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            $message = match ($proceedingJoinPoint->methodName) {
                'fetch' => (($result[0] ?? false) ? 'Read: ' : 'Missed: ') . ($arguments['key'] ?? ''),
                'get' => (! is_null($result) ? 'Read: ' : 'Missed: ') . ($arguments['key'] ?? ''),
                'getMultiple' => 'Read: ' . implode(', ', (array) ($arguments['keys'] ?? [])),
                'set' => 'Written: ' . ($arguments['key'] ?? ''),
                'setMultiple' => 'Written: ' . implode(', ', array_keys($arguments['keys'] ?? [])),
                'delete' => 'Forgotten: ' . ($arguments['key'] ?? ''),
                'deleteMultiple' => 'Forgotten: ' . implode(', ', array_keys($arguments['keys'] ?? [])),
                default => 'Operation',
            };

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'cache',
                $message,
                [
                    'result' => $result,
                    'arguments' => $arguments,
                    'timeMs' => (microtime(true) - $startTime) * 1000,
                ]
            ));
        });
    }
}
