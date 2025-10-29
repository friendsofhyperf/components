<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Collection\LazyCollection;

use function PHPStan\Testing\assertType;

// LazyCollection::isSingle() tests
assertType('bool', LazyCollection::make([1])->isSingle());
assertType('bool', LazyCollection::make([1, 2])->isSingle());
assertType('bool', LazyCollection::make([])->isSingle());

// LazyCollection::collapseWithKeys() tests
assertType('Hyperf\Collection\LazyCollection', LazyCollection::make([['a' => 1], ['b' => 2]])->collapseWithKeys());
assertType('Hyperf\Collection\LazyCollection', LazyCollection::make([])->collapseWithKeys());
