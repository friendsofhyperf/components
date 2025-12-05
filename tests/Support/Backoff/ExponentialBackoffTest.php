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

use FriendsOfHyperf\Support\Backoff\ExponentialBackoff;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class ExponentialBackoffTest extends BackoffTestCase
{
    public function testConstructorWithDefaults()
    {
        $backoff = new ExponentialBackoff();
        $delay = $backoff->next();

        // With jitter enabled by default, we can't predict exact value
        // But it should be around the initial value
        $this->assertGreaterThanOrEqual(50, $delay);
        $this->assertLessThanOrEqual(100, $delay);
    }

    public function testExponentialGrowthWithoutJitter()
    {
        $backoff = new ExponentialBackoff(100, 10000, 2.0, false);

        // Formula: initial * (factor ^ attempt)
        // attempt starts from 0
        $this->assertEquals(100 * (2 ** 0), $backoff->next()); // 100
        $this->assertEquals(100 * (2 ** 1), $backoff->next()); // 200
        $this->assertEquals(100 * (2 ** 2), $backoff->next()); // 400
        $this->assertEquals(100 * (2 ** 3), $backoff->next()); // 800
        $this->assertEquals(100 * (2 ** 4), $backoff->next()); // 1600
    }

    public function testMaximumDelayCap()
    {
        $backoff = new ExponentialBackoff(100, 1000, 2.0, false);

        $delays = [];
        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
            $this->assertLessThanOrEqual(1000, $delay);
        }

        // Check that we actually hit the cap
        $this->assertEquals(1000, $delays[count($delays) - 1]);
    }

    public function testCustomFactor()
    {
        $backoff = new ExponentialBackoff(100, 10000, 3.0, false);

        $this->assertEquals(100 * (3 ** 0), $backoff->next()); // 100
        $this->assertEquals(100 * (3 ** 1), $backoff->next()); // 300
        $this->assertEquals(100 * (3 ** 2), $backoff->next()); // 900
        $this->assertEquals(100 * (3 ** 3), $backoff->next()); // 2700
    }

    public function testJitterRange()
    {
        $backoff = new ExponentialBackoff(200, 10000, 2.0, true);

        // First attempt - jitter should be between 100 and 200
        $delay = $backoff->next();
        $this->assertGreaterThanOrEqual(100, $delay);
        $this->assertLessThanOrEqual(200, $delay);
    }

    public function testJitterPreventsPredictableValues()
    {
        $backoff = new ExponentialBackoff(1000, 100000, 2.0, true);

        // Get multiple values for the same attempt number
        $values = [];
        for ($i = 0; $i < 20; ++$i) {
            $backoff->reset();
            $values[] = $backoff->next();
        }

        // Should have variation due to jitter
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Jitter should produce varied values');
    }

    public function testResetAffectsCalculation()
    {
        $backoff = new ExponentialBackoff(100, 10000, 2.0, false);

        $delay1 = $backoff->next(); // 100
        $delay2 = $backoff->next(); // 200
        $backoff->reset();
        $delay3 = $backoff->next(); // 100 again

        $this->assertEquals(100, $delay1);
        $this->assertEquals(200, $delay2);
        $this->assertEquals(100, $delay3);
    }

    public function testFractionalFactor()
    {
        $backoff = new ExponentialBackoff(1000, 10000, 1.5, false);

        $this->assertEquals(1000 * (1.5 ** 0), $backoff->next()); // 1000
        $this->assertEquals(1000 * (1.5 ** 1), $backoff->next()); // 1500
        $this->assertEquals(1000 * (1.5 ** 2), $backoff->next()); // 2250
    }

    public function testZeroFactor()
    {
        $backoff = new ExponentialBackoff(100, 10000, 0.0, false);

        // With factor 0, all subsequent attempts after first should be 0
        $this->assertEquals(100, $backoff->next()); // First attempt: initial
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals(0, $backoff->next()); // Subsequent: 100 * 0^attempt
        }
    }

    public function testPrivateProperties()
    {
        $backoff = new ExponentialBackoff(150, 15000, 2.5, true);

        $initial = $this->getPrivateProperty($backoff, 'initial');
        $max = $this->getPrivateProperty($backoff, 'max');
        $factor = $this->getPrivateProperty($backoff, 'factor');
        $jitter = $this->getPrivateProperty($backoff, 'jitter');

        $this->assertEquals(150, $initial);
        $this->assertEquals(15000, $max);
        $this->assertEquals(2.5, $factor);
        $this->assertTrue($jitter);
    }

    public function testMaxSmallerThanInitial()
    {
        // Edge case: max is smaller than initial
        $backoff = new ExponentialBackoff(1000, 500, 2.0, false);

        // Should always return max (500) since it's smaller than initial
        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(500, $delay);
        }
    }

    protected function createBackoff(): ExponentialBackoff
    {
        return new ExponentialBackoff(100, 10000, 2.0, false);
    }
}
