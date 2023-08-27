<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;

uses()->group('fast-paginate');

test('test Builder', function () {
    expect(Builder::hasGlobalMacro('fastPaginate'))->toBeTrue();
    expect(Builder::hasGlobalMacro('simpleFastPaginate'))->toBeTrue();
});

test('test Relation', function () {
    expect(Relation::hasMacro('fastPaginate'))->toBeTrue();
    expect(Relation::hasMacro('simpleFastPaginate'))->toBeTrue();
});
