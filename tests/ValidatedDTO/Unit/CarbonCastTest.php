<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonCast;
use FriendsOfHyperf\ValidatedDTO\Casting\CarbonImmutableCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;

beforeEach(function () {
})->skipOnPhp('8.3.20');

it('casts to carbon', function () {
    $castable = new CarbonCast();

    $date = date('Y-m-d');
    $result = $castable->cast(test_property(), $date);
    expect($result)->toBeInstanceOf(Carbon::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $date = date('Y-m-d', strtotime('-1 days'));
    $result = $castable->cast(test_property(), '-1 days');
    expect($result)->toBeInstanceOf(Carbon::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');
});

it('casts to carbon with timezone', function () {
    $castable = new CarbonCast($this->timezone);

    $date = date('Y-m-d');
    $result = $castable->cast(test_property(), $date);
    expect($result)->toBeInstanceOf(Carbon::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $date = date('Y-m-d', strtotime('-1 days'));
    $result = $castable->cast(test_property(), '-1 days');
    expect($result)->toBeInstanceOf(Carbon::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');

    $castable = new CarbonCast($this->timezone, 'Y-m-d');

    $date = date('Y-m-d');
    $result = $castable->cast(test_property(), $date);
    expect($result)->toBeInstanceOf(Carbon::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $date = date('Y-m-d H:i:s');
    $this->expectException(CastException::class);
    $castable->cast(test_property(), $date);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');
})->skipOnPhp('8.3.20');

it('casts to carbon immutable', function () {
    $castable = new CarbonImmutableCast();

    $date = date('Y-m-d');
    $result = $castable->cast(test_property(), $date);
    expect($result)->toBeInstanceOf(CarbonImmutable::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $date = date('Y-m-d', strtotime('-1 days'));
    $result = $castable->cast(test_property(), '-1 days');
    expect($result)->toBeInstanceOf(CarbonImmutable::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');

    $castable = new CarbonImmutableCast($this->timezone);

    $date = date('Y-m-d');
    $result = $castable->cast(test_property(), $date);
    expect($result)->toBeInstanceOf(CarbonImmutable::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $date = date('Y-m-d', strtotime('-1 days'));
    $result = $castable->cast(test_property(), '-1 days');
    expect($result)->toBeInstanceOf(CarbonImmutable::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');

    $castable = new CarbonImmutableCast($this->timezone, 'Y-m-d');

    $date = date('Y-m-d');
    $result = $castable->cast(test_property(), $date);
    expect($result)->toBeInstanceOf(CarbonImmutable::class);
    $result = $result->format('Y-m-d');
    expect($result)->toBe($date);

    $date = date('Y-m-d H:i:s');
    $this->expectException(CastException::class);
    $castable->cast(test_property(), $date);

    $this->expectException(CastException::class);
    $castable->cast(test_property(), 'TEST');
});
