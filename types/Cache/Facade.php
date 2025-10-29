<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Cache\Facade\Cache;

use function PHPStan\Testing\assertType;

// Cache::store() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', Cache::store());
assertType('FriendsOfHyperf\Cache\Contract\Repository', Cache::store('default'));
assertType('FriendsOfHyperf\Cache\Contract\Repository', Cache::store('redis'));

// Cache::driver() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', Cache::driver());
assertType('FriendsOfHyperf\Cache\Contract\Repository', Cache::driver('default'));

// Cache::resolve() tests
assertType('FriendsOfHyperf\Cache\Contract\Repository', Cache::resolve('default'));

// Cache::has() tests
assertType('bool', Cache::has('key'));

// Cache::missing() tests
assertType('bool', Cache::missing('key'));

// Cache::get() tests
assertType('mixed', Cache::get('key'));
assertType('mixed', Cache::get('key', 'default'));
assertType('string', Cache::get('key', 'default'));
assertType('int', Cache::get('key', 123));

// Cache::many() tests
assertType('iterable', Cache::many(['key1', 'key2']));

// Cache::pull() tests
assertType('mixed', Cache::pull('key'));
assertType('mixed', Cache::pull('key', 'default'));
assertType('string', Cache::pull('key', 'default'));

// Cache::put() tests
assertType('bool', Cache::put('key', 'value'));
assertType('bool', Cache::put('key', 'value', 60));
assertType('bool', Cache::put('key', 'value', new DateInterval('PT1H')));
assertType('bool', Cache::put('key', 'value', new DateTime('+1 hour')));

// Cache::putMany() tests
assertType('bool', Cache::putMany(['key1' => 'value1', 'key2' => 'value2']));
assertType('bool', Cache::putMany(['key1' => 'value1'], 60));

// Cache::add() tests
assertType('bool', Cache::add('key', 'value'));
assertType('bool', Cache::add('key', 'value', 60));

// Cache::forever() tests
assertType('bool', Cache::forever('key', 'value'));

// Cache::forget() tests
assertType('bool', Cache::forget('key'));

// Cache::flush() tests
assertType('bool', Cache::flush());

// Cache::increment() tests
assertType('bool|int', Cache::increment('key'));
assertType('bool|int', Cache::increment('key', 5));

// Cache::decrement() tests
assertType('bool|int', Cache::decrement('key'));
assertType('bool|int', Cache::decrement('key', 5));

// Cache::remember() tests
assertType('string', Cache::remember('key', 60, fn () => 'value'));
assertType('int', Cache::remember('key', 60, fn () => 123));
assertType('array', Cache::remember('key', 60, fn () => []));

// Cache::rememberForever() tests
assertType('string', Cache::rememberForever('key', fn () => 'value'));
assertType('int', Cache::rememberForever('key', fn () => 123));

// Cache::sear() tests
assertType('string', Cache::sear('key', fn () => 'value'));
assertType('int', Cache::sear('key', fn () => 123));

// Cache::flexible() tests
assertType('string', Cache::flexible('key', [60, 120], fn () => 'value'));
assertType('int', Cache::flexible('key', [60, 120], fn () => 123));
assertType('array', Cache::flexible('key', [60, 120], fn () => []));
