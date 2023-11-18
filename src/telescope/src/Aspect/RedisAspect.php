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
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Contract\PackerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Collection\collect;
use function Hyperf\Config\config;
use function Hyperf\Tappable\tap;

/**
 * @property string $poolName
 * @property PackerInterface $packer
 */
class RedisAspect extends AbstractAspect
{
    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(protected SwitchManager $switcherManager, protected ContainerInterface $container)
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

    private function formatCommand(string $command, array $parameters): string
    {
        $parameters = collect($parameters)
            ->map(function ($parameter, $key) use ($command) {
                if (is_array($parameter)) {
                    return collect($parameter)
                        ->map(function ($value, $key) {
                            if (is_array($value)) {
                                return json_encode($value);
                            }

                            return is_int($key) ? $value : "{$key} {$value}";
                        })
                        ->implode(' ');
                }
                if (
                    $command == 'set'
                    && $key == 1
                    && $driver = TelescopeContext::getCacheDriver()
                ) {
                    $packer = config('cache.' . $driver . '.packer', '');
                    $packer = $this->container->get($packer);
                    if ($packer instanceof PackerInterface) {
                        try {
                            $unpacked = $packer->unpack((string) $parameter);
                            $parameter = match (true) {
                                is_null($unpacked) => 'null',
                                is_array($unpacked) => json_encode($unpacked),
                                default => $unpacked,
                            };
                        } catch (Throwable $e) {
                        }
                    }
                }
                return $parameter;
            })
            ->implode(' ');

        return "{$command} {$parameters}";
    }
}
