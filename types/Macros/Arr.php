<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Collection\Arr;

use function PHPStan\Testing\assertType;

// Arr::arrayable() tests
assertType('bool', Arr::arrayable([]));
assertType('bool', Arr::arrayable(['key' => 'value']));
assertType('bool', Arr::arrayable('string'));

// Arr::array() tests
assertType('array', Arr::array(['nested' => ['key' => 'value']], 'nested'));
assertType('array', Arr::array(['data' => [1, 2, 3]], 'data', []));

// Arr::boolean() tests
assertType('bool', Arr::boolean(['is_active' => true], 'is_active'));
assertType('bool', Arr::boolean(['flag' => false], 'flag', false));

// Arr::every() tests
assertType('bool', Arr::every([1, 2, 3], fn ($value) => $value > 0));
assertType('bool', Arr::every(['a', 'b', 'c'], fn ($value) => is_string($value)));

// Arr::float() tests
assertType('float', Arr::float(['price' => 10.5], 'price'));
assertType('float', Arr::float(['amount' => 99.99], 'amount', 0.0));

// Arr::from() tests
assertType('array{}', Arr::from([]));
assertType('array<string, string>', Arr::from(['key' => 'value']));

// Arr::hasAll() tests
assertType('bool', Arr::hasAll(['a' => 1, 'b' => 2], ['a', 'b']));
assertType('bool', Arr::hasAll(['x' => 1], 'x'));

// Arr::integer() tests
assertType('int', Arr::integer(['count' => 10], 'count'));
assertType('int', Arr::integer(['total' => 100], 'total', 0));

// Arr::string() tests
assertType('string', Arr::string(['name' => 'test'], 'name'));
assertType('string', Arr::string(['title' => 'value'], 'title', 'default'));

// Arr::some() tests
assertType('bool', Arr::some([1, 2, 3], fn ($value) => $value > 2));
assertType('bool', Arr::some(['a', 'b', 'c'], fn ($value) => $value === 'b'));

// Arr::sortByMany() tests
assertType('array', Arr::sortByMany([['name' => 'John', 'age' => 30]], [['name', true]]));
assertType('array', Arr::sortByMany([['x' => 1], ['x' => 2]], []));
