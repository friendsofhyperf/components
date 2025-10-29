<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Collection\Collection;

use function PHPStan\Testing\assertType;

// Collection::collapseWithKeys() tests
assertType('Hyperf\Collection\Collection', (new Collection([['a' => 1], ['b' => 2]]))->collapseWithKeys());
assertType('Hyperf\Collection\Collection', (new Collection([]))->collapseWithKeys());
