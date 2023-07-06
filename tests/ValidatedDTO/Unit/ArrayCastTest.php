<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Casting\ArrayCast;

it('properly casts from json string to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), '{"name": "John Doe", "email": "john.doe@example.com"}')
    ->toBe(['name' => 'John Doe', 'email' => 'john.doe@example.com']);

it('properly casts from string to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), 'Test')
    ->toBe(['Test']);

it('properly casts from integer to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), 1)
    ->toBe([1]);

it('properly casts from array to array')
    ->expect(fn () => new ArrayCast())
    ->cast(test_property(), ['a', 'A', 1])
    ->toBe(['a', 'A', 1]);
