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

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;

use function Hyperf\Collection\collect;
use function Hyperf\Tappable\tap;

/**
 * @property string $poolName
 */
class RedisAspect extends AbstractAspect
{
    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(protected SwitchManager $switcherManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            if (! $this->switcherManager->isEnable('redis')) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            $commands = $this->formatCommand($arguments['name'], $arguments['arguments']);
            $connection = (fn () => $this->poolName ?? 'default')->call($proceedingJoinPoint->getInstance());

            Telescope::recordRedis(IncomingEntry::make([
                'connection' => $connection,
                'command' => Telescope::getAppName() . $commands,
                'time' => number_format((microtime(true) - $startTime) * 1000, 2, '.', ''),
            ]));
        });
    }

    private function formatCommand($command, $parameters)
    {
        $parameters = collect($parameters)->map(function ($parameter) {
            if (is_array($parameter)) {
                return collect($parameter)->map(function ($value, $key) {
                    if (is_array($value)) {
                        return json_encode($value);
                    }

                    return is_int($key) ? $value : "{$key} {$value}";
                })->implode(' ');
            }

            return $parameter;
        })->implode(' ');

        return "{$command} {$parameters}";
    }
}
