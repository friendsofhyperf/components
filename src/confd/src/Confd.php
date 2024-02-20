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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Engine\Coroutine;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

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
        $this->resolveLogger();
    }

    public function fetch(): array
    {
        return $this->driver->fetch();
    }

    public function watch(): void
    {
        // Fetch first
        Coroutine::create(function () {
            try {
                $this->previous = $this->fetch();
            } catch (Throwable $e) {
                $this->logger?->error($e->getMessage());
            }
        });

        $watches = (array) $this->config->get('confd.watches', []);

        // Loop fetch
        $this->driver->loop(function ($current) use ($watches) {
            foreach ($current as $key => $value) {
                if (isset($this->previous[$key]) && $this->previous[$key] !== $value) {
                    $this->event(new ConfigChanged($current, $this->previous, $changes = array_diff_assoc($current, $this->previous)));

                    if (in_array($key, $watches, true)) {
                        $this->event(new WatchDispatched($changes));
                    }

                    break;
                }
            }

            $this->previous += $current;
        });
    }

    protected function event(object $event): void
    {
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->container->get(EventDispatcherInterface::class)->dispatch($event);
        }
    }
}
