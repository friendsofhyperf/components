<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd;

use FriendsOfHyperf\Confd\Driver\DriverInterface;
use FriendsOfHyperf\Confd\Driver\Etcd;
use FriendsOfHyperf\Confd\Event\ConfigChanged;
use FriendsOfHyperf\Confd\Event\WatchDispatched;
use FriendsOfHyperf\Confd\Traits\Logger;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Timer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Confd
{
    use Logger;

    private DriverInterface $driver;

    private Timer $timer;

    private int $interval;

    private array $previous = [];

    public function __construct(private ContainerInterface $container, private ConfigInterface $config)
    {
        $driver = $this->config->get('confd.default', 'etcd');
        $class = $this->config->get(sprintf('confd.drivers.%s.driver', $driver), Etcd::class);
        $this->driver = $container->get($class);
        $this->interval = (int) $this->config->get('confd.interval', 1);
        $logger = $this->resolveLoggerInstance();
        $this->timer = new Timer($logger);
    }

    public function fetch(): array
    {
        return $this->driver->fetch();
    }

    public function watch(): void
    {
        $watches = (array) $this->config->get('confd.watches', []);

        $this->timer->tick($this->interval, function () use ($watches) {
            $current = $this->driver->fetch();

            if ($this->previous && $changes = array_diff_assoc($current, $this->previous)) {
                $this->event(new ConfigChanged($current, $this->previous, $changes));

                if (Arr::has($changes, $watches)) {
                    $this->event(new WatchDispatched((array) Arr::only($changes, $watches)));
                }
            }

            $this->previous = $current;
        });
    }

    protected function event(object $event): void
    {
        $this->container->get(EventDispatcherInterface::class)?->dispatch($event);
    }
}
