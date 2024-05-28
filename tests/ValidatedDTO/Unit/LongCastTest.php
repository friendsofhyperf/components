<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ValidatedDTO\Casting\LongCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;

it('properly casts to integer')
    ->expect(fn () => new LongCast())
    ->cast(test_property(), '5')
    ->toBe(5);

it('throws exception when it is string')
    ->expect(fn () => new LongCast())
    ->cast(test_property(), 'TEST')
    ->toBe(0);

it('throws exception when it is empty string')
    ->expect(fn () => new LongCast())
    ->cast(test_property(), '')
    ->toBe(0);

it('throws exception when it is unable to cast property')
    ->expect(fn () => new LongCast())
    ->cast(test_property(), new stdClass())
    ->throws(CastException::class, 'Unable to cast property: test_property - invalid value');
