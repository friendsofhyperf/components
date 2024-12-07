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

use FriendsOfHyperf\Telescope\Storage\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Collection\Collection;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Throwable;

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

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected TelescopeConfig $telescopeConfig,
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $startTime = microtime(true);
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint, $startTime) {
            if (
                ! $this->telescopeConfig->isEnable('redis')
                || ! TelescopeContext::getBatchId()
            ) {
                return;
            }

            $arguments = $proceedingJoinPoint->arguments['keys'];
            $commands = $this->formatCommand($arguments['name'], $arguments['arguments']);
            $connection = (fn () => $this->poolName ?? 'default')->call($proceedingJoinPoint->getInstance());

            if (Str::contains($commands, 'telescope')) {
                return;
            }

            Telescope::recordRedis(IncomingEntry::make([
                'connection' => $connection,
                'command' => $commands,
                'time' => number_format((microtime(true) - $startTime) * 1000, 2, '.', ''),
            ]));
        });
    }

    private function formatCommand(string $command, array $parameters): string
    {
        $parameters = (new Collection($parameters))
            ->map(function ($parameter, $key) use ($command) {
                if (is_array($parameter)) {
                    return (new Collection($parameter))
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
                    $packer = $this->config->get('cache.' . $driver . '.packer', '');
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
