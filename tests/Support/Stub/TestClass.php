<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Support\Stub;

class TestClass
{
    protected int $randomNumber;

    public function __construct()
    {
        $this->randomNumber = rand(1, 1000000);
    }

    public function getRandomNumber()
    {
        return once(function () {
            return $this->randomNumber;
        });
    }
}
