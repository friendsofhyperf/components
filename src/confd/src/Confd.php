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
use FriendsOfHyperf\Confd\Traits\Logger;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Confd
{
    use Logger;

    private DriverInterface $driver;

    private array $previous = [];

    public function __construct(private ContainerInterface $container, private ConfigInterface $config)
    {
        $driver = $this->config->get('confd.default', 'etcd');
        $class = $this->config->get(sprintf('confd.drivers.%s.driver', $driver), Etcd::class);
        $this->driver = $container->get($class);
    }

    public function fetch(): array
    {
        return $this->driver->fetch();
    }

    public function watch(): void
    {
        $this->driver->loop(function ($current) {
            foreach ($current as $key => $value) {
                if (isset($this->previous[$key]) && $this->previous[$key] !== $value) {
                    $this->event(new ConfigChanged($current, $this->previous, array_diff_assoc($current, $this->previous)));
                    break;
                }
            }

            $this->previous += $current;
        });
    }

    protected function event(object $event): void
    {
        $this->container->get(EventDispatcherInterface::class)?->dispatch($event);
    }
}
