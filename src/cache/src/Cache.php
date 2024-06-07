<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Cache;

use Carbon\Carbon;
use Closure;
use DateInterval;
use DateTimeInterface;
use FriendsOfHyperf\Cache\Event\CacheHit;
use FriendsOfHyperf\Cache\Event\CacheMissed;
use FriendsOfHyperf\Cache\Event\ForgettingKey;
use FriendsOfHyperf\Cache\Event\KeyForgotten;
use FriendsOfHyperf\Cache\Event\KeyWriteFailed;
use FriendsOfHyperf\Cache\Event\KeyWritten;
use FriendsOfHyperf\Cache\Event\RetrievingKey;
use FriendsOfHyperf\Cache\Event\RetrievingManyKeys;
use FriendsOfHyperf\Cache\Event\WritingKey;
use FriendsOfHyperf\Cache\Event\WritingManyKeys;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Macroable\Macroable;
use Hyperf\Support\Traits\InteractsWithTime;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Collection\value;
use function Hyperf\Support\with;
use function Hyperf\Tappable\tap;

class Cache implements CacheInterface
{
    use InteractsWithTime;
    use Macroable;

    public function __construct(
        protected DriverInterface $driver,
        protected ?EventDispatcherInterface $eventDispatcher = null,
        protected string $name = 'default'
    ) {
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
        return $this->driver->clear();
    }

    public function forever($key, $value): bool
    {
        $this->event(new WritingKey($this->getName(), $key, $value));

        $result = $this->driver->set($key, $value);

        if ($result) {
            $this->event(new KeyWritten($this->getName(), $key, $value));
        } else {
            $this->event(new KeyWriteFailed($this->getName(), $key, $value));
        }

        return $result;
    }

    public function forget($key): bool
    {
        $this->event(new ForgettingKey($this->getName(), $key));

        return tap($this->driver->delete($key), function ($result) use ($key) {
            if ($result) {
                $this->event(new KeyForgotten($this->getName(), $key));
            } else {
                $this->event(new ForgettingKey($this->getName(), $key));
            }
        });
    }

    public function has($key): bool
    {
        return $this->driver->has($key);
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

        $this->event(new WritingKey($this->getName(), $key, $value, $seconds));

        $result = $this->driver->set($key, $value, $seconds);

        if ($result) {
            $this->event(new KeyWritten($this->getName(), $key, $value, $seconds));
        } else {
            $this->event(new KeyWriteFailed($this->getName(), $key, $value, $seconds));
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

        $this->event(new WritingManyKeys($this->getName(), array_keys($values), array_values($values), $seconds));

        $result = $this->driver->setMultiple($values, $seconds);

        foreach ($values as $key => $value) {
            if ($result) {
                $this->event(new KeyWritten($this->getName(), $key, $value, $seconds));
            } else {
                $this->event(new KeyWriteFailed($this->getName(), $key, $value, $seconds));
            }
        }

        return $result;
    }

    public function decrement($key, $value = 1)
    {
        return with((int) $this->get($key, 0) - (int) $value, fn ($value) => ! $this->put($key, $value) ? false : $value);
    }

    public function increment($key, $value = 1)
    {
        return with((int) $this->get($key, 0) + (int) $value, fn ($value) => ! $this->put($key, $value) ? false : $value);
    }

    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->many($key);
        }

        $value = $this->driver->get($key);

        if (is_null($value)) {
            $this->event(new CacheMissed($this->getName(), $key));

            $value = value($default);
        } else {
            $this->event(new CacheHit($this->getName(), $key, $value));
        }

        $this->event(new RetrievingKey($this->getName(), $key));

        return $value;
    }

    public function many(array $keys)
    {
        $this->event(new RetrievingManyKeys($this->getName(), $keys));

        $values = $this->driver
            ->getMultiple(
                collect($keys)
                    ->map(fn ($value, $key) => is_string($key) ? $key : $value)
                    ->values()
                    ->all()
            );

        return collect($values)
            ->map(fn ($value, $key) => $this->handleManyResult($keys, $key, $value))
            ->all();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $defaults = [];

        foreach ($keys as $key) {
            $defaults[$key] = $default;
        }

        return $this->many($defaults);
    }

    public function pull($key, $default = null)
    {
        return tap($this->get($key), fn () => $this->forget($key));
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

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->putMany(is_array($values) ? $values : iterator_to_array($values), $ttl);
    }

    public function deleteMultiple(array $keys)
    {
        $result = true;

        foreach ($keys as $key) {
            if (! $this->forget($key)) {
                $result = false;
            }
        }

        return $result;
    }

    protected function getName(): string
    {
        return $this->name;
    }

    /**
     * Handle a result for the "many" method.
     *
     * @param array $keys
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function handleManyResult($keys, $key, $value)
    {
        // If we could not find the cache value, we will fire the missed event and get
        // the default value for this cache value. This default could be a callback
        // so we will execute the value function which will resolve it if needed.
        if (is_null($value)) {
            $this->event(new CacheMissed($this->getName(), $key));

            return (isset($keys[$key]) && ! array_is_list($keys)) ? value($keys[$key]) : null;
        }

        // If we found a valid value we will fire the "hit" event and return the value
        // back from this function. The "hit" event gives developers an opportunity
        // to listen for every possible cache "hit" throughout this applications.
        $this->event(new CacheHit($this->getName(), $key, $value));

        return $value;
    }

    protected function putManyForever(array $values)
    {
        $result = $this->driver->setMultiple($values);

        if ($result) {
            foreach ($values as $key => $value) {
                $this->event(new KeyWritten($this->getName(), $key, $value));
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

    /**
     * Fire an event for this cache instance.
     *
     * @param object $event
     */
    protected function event($event)
    {
        $this->eventDispatcher?->dispatch($event);
    }
}
