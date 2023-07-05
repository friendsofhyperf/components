<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;

it('casts property to object using callback function')
    ->expect(function () {
        $callback = function (string $property, mixed $value) {
            if (is_string($value)) {
                $value = json_decode($value, true);
            }

            if (! is_array($value)) {
                throw new CastException($property);
            }

            return $value;
        };

        return $callback(test_property(), '{"name": "John Doe", "email": "john.doe@example.com"}');
    })
    ->toBe(['name' => 'John Doe', 'email' => 'john.doe@example.com']);

it('casts property to uppercase using callback function')
    ->expect(function () {
        $callback = fn (string $property, mixed $value) => strtoupper($value);

        return $callback(test_property(), 'John Doe');
    })
    ->toBe('JOHN DOE');
