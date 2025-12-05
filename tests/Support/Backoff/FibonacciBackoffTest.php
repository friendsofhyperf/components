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

use FriendsOfHyperf\Support\Backoff\FibonacciBackoff;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class FibonacciBackoffTest extends BackoffTestCase
{
    public function testConstructorWithDefaultMax()
    {
        $backoff = new FibonacciBackoff();
        // Default max should be 10000
        $delay = $backoff->next();
        $this->assertEquals(1, $delay);
    }

    public function testFibonacciSequence()
    {
        $backoff = new FibonacciBackoff(10000);

        // Fibonacci sequence: 1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89, 144...
        $expected = [1, 1, 2, 3, 5, 8, 13, 21, 34, 55];

        for ($i = 0; $i < count($expected); ++$i) {
            $delay = $backoff->next();
            $this->assertEquals($expected[$i], $delay);
        }
    }

    public function testMaximumDelayCap()
    {
        $backoff = new FibonacciBackoff(50);

        // Generate delays until we hit the cap
        $delays = [];
        $hasCapped = false;

        for ($i = 0; $i < 20; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;

            if ($delay === 50) {
                $hasCapped = true;
            }

            $this->assertLessThanOrEqual(50, $delay);
        }

        $this->assertTrue($hasCapped, 'Should have reached the maximum cap');
        $this->assertEquals(50, $delays[count($delays) - 1]);
    }

    public function testCustomMax()
    {
        $backoff = new FibonacciBackoff(100);

        // Generate until we exceed or hit 100
        $delays = [];
        for ($i = 0; $i < 15; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
            $this->assertLessThanOrEqual(100, $delay);
        }

        // Verify we hit the cap
        $this->assertEquals(100, $delays[count($delays) - 1]);
    }

    public function testResetResetsSequence()
    {
        $backoff = new FibonacciBackoff(10000);

        // Get first few values
        $values = [];
        for ($i = 0; $i < 5; ++$i) {
            $values[] = $backoff->next();
        }

        // Reset and get values again
        $backoff->reset();
        $resetValues = [];
        for ($i = 0; $i < 5; ++$i) {
            $resetValues[] = $backoff->next();
        }

        // Should be the same sequence
        $this->assertEquals($values, $resetValues);
    }

    public function testResetAffectsFibonacciState()
    {
        $backoff = new FibonacciBackoff(10000);

        // Generate some values to change the internal state
        $backoff->next(); // 1
        $backoff->next(); // 1
        $backoff->next(); // 2
        $backoff->next(); // 3
        $backoff->next(); // 5

        // Reset should restore initial state
        $backoff->reset();
        $delay = $backoff->next();
        $this->assertEquals(1, $delay);
    }

    public function testLargeFibonacciNumbers()
    {
        $backoff = new FibonacciBackoff(1000000);

        // Generate some larger Fibonacci numbers
        $delays = [];
        for ($i = 0; $i < 20; ++$i) {
            $delays[] = $backoff->next();
        }

        // Verify the sequence is correct for known values
        $this->assertEquals(6765, $delays[19]); // F(20) = 6765
    }

    public function testPrivateProperties()
    {
        $backoff = new FibonacciBackoff(5000);

        $max = $this->getPrivateProperty($backoff, 'max');
        $attempt = $this->getPrivateProperty($backoff, 'attempt');
        $prev = $this->getPrivateProperty($backoff, 'prev');
        $curr = $this->getPrivateProperty($backoff, 'curr');

        $this->assertEquals(5000, $max);
        $this->assertEquals(0, $attempt);
        $this->assertEquals(0, $prev);
        $this->assertEquals(1, $curr);
    }

    public function testPrivatePropertiesAfterOperations()
    {
        $backoff = new FibonacciBackoff(10000);

        $backoff->next(); // Move to first Fibonacci number
        $backoff->next(); // Move to second Fibonacci number

        $attempt = $this->getPrivateProperty($backoff, 'attempt');
        $prev = $this->getPrivateProperty($backoff, 'prev');
        $curr = $this->getPrivateProperty($backoff, 'curr');

        $this->assertEquals(2, $attempt);
        $this->assertEquals(1, $prev); // Should be F(1)
        $this->assertEquals(2, $curr); // Should be F(2)
    }

    public function testZeroMax()
    {
        $backoff = new FibonacciBackoff(0);

        // With max 0, all delays should be 0
        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(0, $delay);
        }
    }

    public function testNegativeMax()
    {
        // Edge case: negative max value
        $backoff = new FibonacciBackoff(-100);

        // Should cap at 0 (can't have negative delays)
        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(0, $delay);
        }
    }

    protected function createBackoff(): FibonacciBackoff
    {
        return new FibonacciBackoff(10000);
    }
}
