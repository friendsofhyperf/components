<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use Hyperf\Stringable\Stringable;
use Mockery as m;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use Concerns\InteractsWithContainer;
    use RunTestsInCoroutine;

    protected function setUp(): void
    {
        $bootApplication = (object) [];
        (new \FriendsOfHyperf\Macros\Listener\RegisterMixinListener())->process($bootApplication);
        (new \FriendsOfHyperf\FastPaginate\Listener\RegisterMixinListener())->process($bootApplication);

        $this->refreshContainer();
    }

    protected function tearDown(): void
    {
        m::close();

        $this->flushContainer();
    }

    protected function stringable($value = '')
    {
        return new Stringable($value);
    }
}
