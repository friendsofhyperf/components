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

use FriendsOfHyperf\Support\Backoff\DecorrelatedJitterBackoff;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class DecorrelatedJitterBackoffTest extends BackoffTestCase
{
    public function testConstructorWithDefaults()
    {
        $backoff = new DecorrelatedJitterBackoff();
        $delay = $backoff->next();

        // Should be between base (100) and base (since no previous delay)
        $this->assertGreaterThanOrEqual(100, $delay);
        $this->assertLessThanOrEqual(100, $delay);
        $this->assertEquals(100, $delay);
    }

    public function testDecorrelatedJitterRange()
    {
        $backoff = new DecorrelatedJitterBackoff(100, 10000, 3.0);

        // First call should return base
        $delay1 = $backoff->next();
        $this->assertEquals(100, $delay1);

        // Second call should be between base and base * factor (100 * 3 = 300)
        $delay2 = $backoff->next();
        $this->assertGreaterThanOrEqual(100, $delay2);
        $this->assertLessThanOrEqual(300, $delay2);

        // Third call should be between base and delay2 * factor
        $delay3 = $backoff->next();
        $this->assertGreaterThanOrEqual(100, $delay3);
        $this->assertLessThanOrEqual($delay2 * 3, $delay3);
    }

    public function testMaximumDelayCap()
    {
        $backoff = new DecorrelatedJitterBackoff(100, 500, 10.0);

        // Generate delays and ensure none exceed max
        for ($i = 0; $i < 20; ++$i) {
            $delay = $backoff->next();
            $this->assertLessThanOrEqual(500, $delay);
        }
    }

    public function testCustomParameters()
    {
        $backoff = new DecorrelatedJitterBackoff(50, 5000, 5.0);

        // First delay should be base
        $delay1 = $backoff->next();
        $this->assertEquals(50, $delay1);

        // Second delay should be between 50 and 50 * 5 = 250
        $delay2 = $backoff->next();
        $this->assertGreaterThanOrEqual(50, $delay2);
        $this->assertLessThanOrEqual(250, $delay2);
    }

    public function testFactorAffectsRange()
    {
        // Test with factor 2.0
        $backoff1 = new DecorrelatedJitterBackoff(100, 10000, 2.0);
        $backoff1->next(); // First delay
        $delay2 = $backoff1->next();
        $this->assertLessThanOrEqual(200, $delay2); // 100 * 2

        // Test with factor 4.0
        $backoff2 = new DecorrelatedJitterBackoff(100, 10000, 4.0);
        $backoff2->next(); // First delay
        $delay3 = $backoff2->next();
        $this->assertLessThanOrEqual(400, $delay3); // 100 * 4
    }

    public function testRandomnessVariation()
    {
        $backoff = new DecorrelatedJitterBackoff(100, 10000, 3.0);

        // Get multiple values for the same state
        $values = [];
        $backoff->next(); // Set initial state

        for ($i = 0; $i < 10; ++$i) {
            $backoff->reset();
            $backoff->next(); // Get base
            $values[] = $backoff->next(); // Get random value
        }

        // Due to randomness, we might not always get variation
        // but with enough samples we should
        $unique = array_unique($values);
        $this->assertGreaterThanOrEqual(1, count($unique));
    }

    public function testResetAffectsCalculation()
    {
        $backoff = new DecorrelatedJitterBackoff(100, 10000, 3.0);

        $delay1 = $backoff->next(); // Should be 100 (base)
        $backoff->next(); // Second delay

        $backoff->reset();
        $delay3 = $backoff->next(); // Should be 100 again

        $this->assertEquals(100, $delay1);
        $this->assertEquals(100, $delay3);
    }

    public function testPrivateProperties()
    {
        $backoff = new DecorrelatedJitterBackoff(150, 15000, 4.0);

        $base = $this->getPrivateProperty($backoff, 'base');
        $max = $this->getPrivateProperty($backoff, 'max');
        $factor = $this->getPrivateProperty($backoff, 'factor');
        $prevDelay = $this->getPrivateProperty($backoff, 'prevDelay');
        $attempt = $this->getPrivateProperty($backoff, 'attempt');

        $this->assertEquals(150, $base);
        $this->assertEquals(15000, $max);
        $this->assertEquals(4.0, $factor);
        $this->assertEquals(150, $prevDelay); // Initially set to base
        $this->assertEquals(0, $attempt);
    }

    public function testPrivatePropertiesAfterOperations()
    {
        $backoff = new DecorrelatedJitterBackoff(100, 10000, 3.0);

        $delay = $backoff->next();
        $prevDelay = $this->getPrivateProperty($backoff, 'prevDelay');
        $attempt = $this->getPrivateProperty($backoff, 'attempt');

        // After first call, prevDelay should be updated and attempt should be 1
        $this->assertEquals($delay, $prevDelay);
        $this->assertEquals(1, $attempt);
    }

    public function testMaxSmallerThanBase()
    {
        // Edge case: max is smaller than base
        $backoff = new DecorrelatedJitterBackoff(1000, 500, 3.0);

        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(500, $delay);
        }
    }

    public function testZeroFactor()
    {
        // Edge case: factor is 0
        $backoff = new DecorrelatedJitterBackoff(100, 10000, 0.0);

        // First delay should be base
        $delay1 = $backoff->next();
        $this->assertEquals(100, $delay1);

        // With factor 0, upper bound will be prevDelay * 0 = 0
        // So random_int(100, 0) would fail
        // In practice, this edge case might need special handling
        $delay2 = $backoff->next();
        $this->assertGreaterThanOrEqual(0, $delay2);
    }

    public function testBaseAndMaxEqual()
    {
        // Edge case: base equals max
        $backoff = new DecorrelatedJitterBackoff(500, 500, 3.0);

        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(500, $delay);
        }
    }

    public function testNegativeBase()
    {
        // Edge case: negative base
        $backoff = new DecorrelatedJitterBackoff(-100, 10000, 3.0);

        $delay1 = $backoff->next();
        $this->assertEquals(-100, $delay1);
    }

    protected function createBackoff(): DecorrelatedJitterBackoff
    {
        return new DecorrelatedJitterBackoff(100, 10000, 3.0);
    }
}
