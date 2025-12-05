<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Support\Backoff;

use FriendsOfHyperf\Support\Backoff\FixedBackoff;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class FixedBackoffTest extends BackoffTestCase
{
    public function testConstructorWithDefaultDelay()
    {
        $backoff = new FixedBackoff();
        $delay = $backoff->next();
        $this->assertEquals(500, $delay);
    }

    public function testConstructorWithCustomDelay()
    {
        $backoff = new FixedBackoff(1000);
        $delay = $backoff->next();
        $this->assertEquals(1000, $delay);
    }

    public function testAlwaysReturnsSameDelay()
    {
        $backoff = new FixedBackoff(750);

        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(750, $delay);
        }
    }

    public function testDelayIsUnaffectedByAttemptCount()
    {
        $backoff = new FixedBackoff(300);

        // Get delay at different attempt counts
        $delay1 = $backoff->next(); // attempt 1
        $delay2 = $backoff->next(); // attempt 2
        $delay3 = $backoff->next(); // attempt 3

        $this->assertEquals(300, $delay1);
        $this->assertEquals(300, $delay2);
        $this->assertEquals(300, $delay3);
    }

    public function testResetMaintainsSameDelay()
    {
        $backoff = new FixedBackoff(250);

        $delay1 = $backoff->next();
        $backoff->next();
        $backoff->reset();
        $delay2 = $backoff->next();

        $this->assertEquals(250, $delay1);
        $this->assertEquals(250, $delay2);
    }

    public function testPrivateDelayProperty()
    {
        $backoff = new FixedBackoff(123);
        $delay = $this->getPrivateProperty($backoff, 'delay');
        $this->assertEquals(123, $delay);
    }

    public function testZeroDelay()
    {
        $backoff = new FixedBackoff(0);

        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(0, $delay);
        }
    }

    public function testNegativeDelay()
    {
        // Test with negative delay - should coerce to zero
        $backoff = new FixedBackoff(-100);
        $delay = $backoff->next();
        $this->assertEquals(0, $delay);
    }

    protected function createBackoff(): FixedBackoff
    {
        return new FixedBackoff(500);
    }
}
