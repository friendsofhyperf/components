<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests;

use Mockery;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void
    {
        $bootApplication = (object) [];
        (new \FriendsOfHyperf\Macros\Listener\RegisterMixinListener())->process($bootApplication);
        (new \FriendsOfHyperf\FastPaginate\Listener\RegisterMixinListener())->process($bootApplication);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
