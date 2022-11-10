<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache;

use Carbon\Carbon;
use Closure;
use DateInterval;
use DateTimeInterface;
use FriendsOfHyperf\Cache\Event\CacheHit;
use FriendsOfHyperf\Cache\Event\CacheMissed;
use FriendsOfHyperf\Cache\Event\KeyForgotten;
use FriendsOfHyperf\Cache\Event\KeyWritten;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Utils\InteractsWithTime;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Cache implements CacheInterface
{
    use InteractsWithTime;

    /**
     * @var DriverInterface
     */
    protected $cacheDriver;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ContainerInterface $container, DriverInterface $driver)
    {
        $this->cacheDriver = $driver;
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
    }

    public function add($key, $value, $ttl = null): bool
    {
        if (is_null($this->get($key))) {
            return $this->put($key, $value, $ttl);
        }

        return false;
    }

    public function flush(): bool
    {
        return $this->cacheDriver->clear();
    }

    public function forever($key, $value): bool
    {
        $result = $this->cacheDriver->set($key, $value);

        if ($result) {
            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new KeyWritten($key, $value));
            }
        }

        return $result;
    }

    public function forget($key): bool
    {
        return tap($this->cacheDriver->delete($key), function () use ($key) {
            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new KeyForgotten($key));
            }
        });
    }

    public function has($key): bool
    {
        return $this->cacheDriver->has($key);
    }

    public function missing($key): bool
    {
        return ! $this->has($key);
    }

    public function put($key, $value, $ttl = null): bool
    {
        if (is_array($key)) {
            return $this->putMany($key, $value);
        }

        if ($ttl === null) {
            return $this->forever($key, $value);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->forget($key);
        }

        $result = $this->cacheDriver->set($key, $value, $seconds);

        if ($result) {
            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new KeyWritten($key, $value, $seconds));
            }
        }

        return $result;
    }

    public function putMany(array $values, $ttl = null): bool
    {
        if ($ttl === null) {
            return $this->putManyForever($values);
        }

        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0) {
            return $this->deleteMultiple(array_keys($values));
        }

        $result = $this->cacheDriver->setMultiple($values, $seconds);

        if ($result) {
            foreach ($values as $key => $value) {
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(new KeyWritten($key, $value, $seconds));
                }
            }
        }

        return $result;
    }

    public function decrement($key, $value = 1)
    {
        return with((int) $this->get($key, 0) - (int) $value, function ($value) use ($key) {
            if (! $this->put($key, $value)) {
                return false;
            }

            return $value;
        });
    }

    public function increment($key, $value = 1)
    {
        return with((int) $this->get($key, 0) + (int) $value, function ($value) use ($key) {
            if (! $this->put($key, $value)) {
                return false;
            }

            return $value;
        });
    }

    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $value = $this->cacheDriver->get($key);

        $callbacks = [];

        if (is_null($value)) {
            $callbacks[] = function () use ($key) {
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(new CacheMissed($key));
                }
            };

            $value = value($default);
        } else {
            $callbacks[] = function () use ($key, $value) {
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(new CacheHit($key, $value));
                }
            };
        }

        parallel($callbacks);

        return $value;
    }

    public function many(array $keys)
    {
        $values = $this->cacheDriver->getMultiple($keys);

        foreach ($values as $key => $value) {
            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(new CacheHit($key, $value));
            }
        }

        return $values;
    }

    public function pull($key, $default = null)
    {
        return tap($this->get($key), function () use ($key) {
            $this->forget($key);
        });
    }

    public function remember($key, $ttl, Closure $callback)
    {
        $value = $this->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $ttl);

        return $value;
    }

    public function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    public function sear($key, Closure $callback)
    {
        return $this->rememberForever($key, $callback);
    }

    protected function deleteMultiple(array $keys)
    {
        $result = $this->cacheDriver->deleteMultiple($keys);

        if ($result) {
            foreach ($keys as $key) {
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(new KeyForgotten($key));
                }
            }
        }

        return true;
    }

    protected function putManyForever(array $values)
    {
        $result = $this->cacheDriver->setMultiple($values);

        if ($result) {
            foreach ($values as $key => $value) {
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(new KeyWritten($key, $value));
                }
            }
        }

        return $result;
    }

    /**
     * Calculate the number of seconds for the given TTL.
     *
     * @param DateInterval|DateTimeInterface|int $ttl
     * @return int
     */
    protected function getSeconds($ttl)
    {
        $duration = $this->parseDateInterval($ttl);

        if ($duration instanceof DateTimeInterface) {
            $duration = Carbon::now()->diffInRealSeconds($duration, false);
        }

        return (int) $duration > 0 ? $duration : 0;
    }
}
