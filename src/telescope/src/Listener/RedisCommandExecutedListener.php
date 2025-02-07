<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Collection\Collection;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Redis\Event\CommandExecuted;
use Psr\Container\ContainerInterface;
use Throwable;

class RedisCommandExecutedListener implements ListenerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ConfigInterface $config,
        private TelescopeConfig $telescopeConfig,
    ) {
        $this->telescopeConfig->isEnable('redis') && $this->setRedisEventEnable();
    }

    public function listen(): array
    {
        return [
            CommandExecuted::class,
        ];
    }

    /**
     * @param object|CommandExecuted $event
     */
    public function process(object $event): void
    {
        if (
            ! $event instanceof CommandExecuted
            || ! $this->telescopeConfig->isEnable('redis')
            || ! TelescopeContext::getBatchId()
        ) {
            return;
        }

        $command = $this->formatCommand($event->command, $event->parameters);

        if (str_contains($command, 'telescope')) {
            return;
        }

        Telescope::recordRedis(IncomingEntry::make([
            'connection' => $event->connection,
            'command' => $command,
            'time' => number_format($event->time * 1000, 2, '.', ''),
        ]));
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
                    $packerClass = $this->config->get('cache.' . $driver . '.packer', '');
                    $packer = $this->container->has($packerClass) ? $this->container->get($packerClass) : null;
                    if ($packer && $packer instanceof PackerInterface) {
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

    private function setRedisEventEnable()
    {
        foreach ((array) $this->config->get('redis', []) as $connection => $_) {
            $this->config->set('redis.' . $connection . '.event.enable', true);
        }
    }
}
