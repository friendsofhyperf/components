<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;

uses(\FriendsOfHyperf\Tests\TestCase::class);

test('test Builder', function () {
    expect(Builder::hasGlobalMacro('fastPaginate'))->toBeTrue();
    expect(Builder::hasGlobalMacro('simpleFastPaginate'))->toBeTrue();
});

test('test Relation', function () {
    expect(Relation::hasMacro('fastPaginate'))->toBeTrue();
    expect(Relation::hasMacro('simpleFastPaginate'))->toBeTrue();
});
