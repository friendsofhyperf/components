<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd;

use FriendsOfHyperf\Confd\Driver\DriverInterface;
use FriendsOfHyperf\Confd\Driver\Etcd;
use FriendsOfHyperf\Confd\Event\ConfigChanged;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Timer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Confd
{
    private DriverInterface $driver;

    private Timer $timer;

    private int $interval;

    public function __construct(private ContainerInterface $container, private ConfigInterface $config)
    {
        $driver = $this->config->get('confd.default', 'etcd');
        $class = $this->config->get(sprintf('confd.drivers.%s.driver', $driver), Etcd::class);
        $this->driver = $container->get($class);
        $this->interval = (int) $this->config->get('confd.interval', 1);
        $this->timer = new Timer();
    }

    public function fetch(): array
    {
        return $this->driver->fetch();
    }

    public function watch(): void
    {
        $this->timer->tick($this->interval, function () {
            if ($changes = $this->driver->getChanges()) {
                $this->container->get(EventDispatcherInterface::class)->dispatch(new ConfigChanged($changes));
            }
        });
    }
}
