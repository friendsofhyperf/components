<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;

it('properly casts to integer')
    ->expect(fn () => new IntegerCast())
    ->cast(test_property(), '5')
    ->toBe(5);

it('throws exception when it is unable to cast property')
    ->expect(fn () => new IntegerCast())
    ->cast(test_property(), 'TEST')
    ->throws(CastException::class, 'Unable to cast property: test_property - invalid value');
