<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Cache\Contract\Repository;
use FriendsOfHyperf\Cache\Repository as CacheRepository;
use Hyperf\Cache\Driver\DriverInterface;

use function PHPStan\Testing\assertType;

/** @var Repository $repository */
$repository = null;

/** @var CacheRepository $cacheRepository */
$cacheRepository = null;

// Repository::has() tests
assertType('bool', $repository->has('key'));

// Repository::missing() tests
assertType('bool', $repository->missing('key'));

// Repository::get() tests
assertType('mixed', $repository->get('key'));
assertType('mixed', $repository->get('key', 'default'));

// Repository::set() tests
assertType('bool', $repository->set('key', 'value'));
assertType('bool', $repository->set('key', 'value', 60));

// Repository::delete() tests
assertType('bool', $repository->delete('key'));

// Repository::clear() tests
assertType('bool', $repository->clear());

// Repository::many() tests
assertType('iterable', $repository->many(['key1', 'key2']));

// Repository::getMultiple() tests
assertType('iterable', $repository->getMultiple(['key1', 'key2']));
assertType('iterable', $repository->getMultiple(['key1', 'key2'], 'default'));

// Repository::setMultiple() tests
assertType('bool', $repository->setMultiple(['key1' => 'value1', 'key2' => 'value2']));
assertType('bool', $repository->setMultiple(['key1' => 'value1'], 60));

// Repository::deleteMultiple() tests
assertType('bool', $repository->deleteMultiple(['key1', 'key2']));

// Repository::pull() tests
assertType('mixed', $repository->pull('key'));
assertType('mixed', $repository->pull('key', 'default'));

// Repository::put() tests
assertType('bool', $repository->put('key', 'value'));
assertType('bool', $repository->put('key', 'value', 60));
assertType('bool', $repository->put(['key1' => 'value1', 'key2' => 'value2'], 60));

// Repository::putMany() tests
assertType('bool', $repository->putMany(['key1' => 'value1', 'key2' => 'value2']));
assertType('bool', $repository->putMany(['key1' => 'value1'], 60));

// Repository::add() tests
assertType('bool', $repository->add('key', 'value'));
assertType('bool', $repository->add('key', 'value', 60));

// Repository::forever() tests
assertType('bool', $repository->forever('key', 'value'));

// Repository::forget() tests
assertType('bool', $repository->forget('key'));

// Repository::flush() tests
assertType('bool', $repository->flush());

// Repository::increment() tests
assertType('bool|int', $repository->increment('key'));
assertType('bool|int', $repository->increment('key', 5));

// Repository::decrement() tests
assertType('bool|int', $repository->decrement('key'));
assertType('bool|int', $repository->decrement('key', 5));

// Repository::remember() tests
assertType('string', $repository->remember('key', 60, fn () => 'value'));
assertType('int', $repository->remember('key', 60, fn () => 123));
assertType('array', $repository->remember('key', 60, fn () => []));

// Repository::rememberForever() tests
assertType('string', $repository->rememberForever('key', fn () => 'value'));
assertType('int', $repository->rememberForever('key', fn () => 123));

// Repository::sear() tests
assertType('string', $repository->sear('key', fn () => 'value'));
assertType('int', $repository->sear('key', fn () => 123));

// Repository::flexible() tests
assertType('string', $repository->flexible('key', [60, 120], fn () => 'value'));
assertType('int', $repository->flexible('key', [60, 120], fn () => 123));
assertType('array', $repository->flexible('key', [60, 120], fn () => []));

// Repository::getDriver() tests
assertType('Hyperf\Cache\Driver\DriverInterface', $repository->getDriver());

// Repository::getStore() tests
assertType('Hyperf\Cache\Driver\DriverInterface', $repository->getStore());

// CacheRepository specific tests
assertType('Hyperf\Cache\Driver\DriverInterface', $cacheRepository->getDriver());
assertType('Hyperf\Cache\Driver\DriverInterface', $cacheRepository->getStore());
