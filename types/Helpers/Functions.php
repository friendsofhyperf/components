<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Support\Fluent;

use function FriendsOfHyperf\Helpers\app;
use function FriendsOfHyperf\Helpers\base_path;
use function PHPStan\Testing\assertType;

assertType('string', base_path());
assertType('string', base_path('foo/bar'));

assertType('mixed', app());
assertType('mixed', app(Fluent::class));
