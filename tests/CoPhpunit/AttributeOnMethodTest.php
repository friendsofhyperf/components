<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\CoPhpunit;

use FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine;
use FriendsOfHyperf\Tests\TestCase;
use Swoole\Coroutine;

/**
 * @internal
 * @coversNothing
 */
class AttributeOnMethodTest extends TestCase
{
    #[NonCoroutine]
    public function testWithNonCoroutineAttribute()
    {
        $this->assertTrue(Coroutine::getCid() === -1);
    }

    public function testWithoutNonCoroutineAttribute()
    {
        $this->assertTrue(Coroutine::getCid() !== -1);
    }
}
