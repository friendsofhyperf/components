<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;

it('properly casts to string')
    ->expect(fn () => new StringCast())
    ->cast(test_property(), 5)->toBe('5')
    ->cast(test_property(), 10.5)->toBe('10.5')
    ->cast(test_property(), true)->toBe('1')
    ->cast(test_property(), false)->toBe('');

it('throws exception when it is unable to cast property')
    ->expect(fn () => new StringCast())
    ->cast(test_property(), ['name' => 'John Doe'])
    ->throws(CastException::class, 'Unable to cast property: test_property - invalid value');
