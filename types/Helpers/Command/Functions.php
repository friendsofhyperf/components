<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

use function FriendsOfHyperf\Helpers\Command\call;
use function PHPStan\Testing\assertType;

// call() tests
assertType('int', call('command:name'));
assertType('int', call('command:name', ['arg' => 'value']));
