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

    public function testSleep()
    {
        $backoff = new FixedBackoff(100); // 100ms delay

        // Test sleep returns correct delay
        $start = microtime(true);
        $delay = $backoff->sleep();
        $end = microtime(true);

        // Should return the delay value
        $this->assertEquals(100, $delay);

        // Should have slept for approximately 100ms (allowing some variance)
        $elapsedMs = (int) (($end - $start) * 1000);
        $this->assertGreaterThanOrEqual(90, $elapsedMs); // Allow 10ms variance
        $this->assertLessThanOrEqual(150, $elapsedMs);   // Allow 50ms variance for system load

        // Should increment attempt counter
        $this->assertEquals(1, $backoff->getAttempt());
    }

    public function testSleepWithZeroDelay()
    {
        $backoff = new FixedBackoff(0);

        $start = microtime(true);
        $delay = $backoff->sleep();
        $end = microtime(true);

        // Should return 0 delay
        $this->assertEquals(0, $delay);

        // Should not sleep (or sleep for negligible time)
        $elapsedMs = (int) (($end - $start) * 1000);
        $this->assertLessThan(10, $elapsedMs); // Less than 10ms
    }

    protected function createBackoff(): FixedBackoff
    {
        return new FixedBackoff(500);
    }
}
