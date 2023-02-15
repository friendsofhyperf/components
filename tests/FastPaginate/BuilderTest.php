<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\FastPaginate;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;

/**
 * @internal
 * @coversNothing
 */
class BuilderTest extends \FriendsOfHyperf\Tests\TestCase
{
    public function testBuilder()
    {
        $this->assertTrue(Builder::hasGlobalMacro('fastPaginate'));
        $this->assertTrue(Builder::hasGlobalMacro('simpleFastPaginate'));
    }

    public function testRelation()
    {
        $this->assertTrue(Relation::hasMacro('fastPaginate'));
        $this->assertTrue(Relation::hasMacro('simpleFastPaginate'));
    }
}
