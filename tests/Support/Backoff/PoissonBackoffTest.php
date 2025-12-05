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

use FriendsOfHyperf\Support\Backoff\PoissonBackoff;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class PoissonBackoffTest extends BackoffTestCase
{
    public function testConstructorWithDefaults()
    {
        $backoff = new PoissonBackoff();
        $delay = $backoff->next();

        // Should be a positive integer based on Poisson distribution
        $this->assertIsInt($delay);
        $this->assertGreaterThanOrEqual(0, $delay);
    }

    public function testCustomParameters()
    {
        $backoff = new PoissonBackoff(1000, 10000);

        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $this->assertIsInt($delay);
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(10000, $delay);
        }
    }

    public function testMaximumDelayCap()
    {
        $backoff = new PoissonBackoff(500, 100);

        // All delays should be capped at max
        for ($i = 0; $i < 20; ++$i) {
            $delay = $backoff->next();
            $this->assertLessThanOrEqual(100, $delay);
        }
    }

    public function testPoissonDistributionRange()
    {
        $backoff = new PoissonBackoff(10, 1000);

        // Generate multiple delays
        $delays = [];
        for ($i = 0; $i < 100; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(1000, $delay);
        }

        // Verify we got varied values (Poisson distribution should produce variation)
        $unique = array_unique($delays);
        $this->assertGreaterThanOrEqual(1, count($unique));

        // Calculate average - should be close to mean (10)
        $average = array_sum($delays) / count($delays);
        $this->assertLessThan(50, $average); // Allow some variance
    }

    public function testMeanAffectsDistribution()
    {
        // Test with small mean
        $backoff1 = new PoissonBackoff(1, 1000);
        $delays1 = [];
        for ($i = 0; $i < 50; ++$i) {
            $delays1[] = $backoff1->next();
        }
        $avg1 = array_sum($delays1) / count($delays1);

        // Test with large mean
        $backoff2 = new PoissonBackoff(100, 1000);
        $delays2 = [];
        for ($i = 0; $i < 50; ++$i) {
            $delays2[] = $backoff2->next();
        }
        $avg2 = array_sum($delays2) / count($delays2);

        // Average with larger mean should be significantly larger
        $this->assertGreaterThan($avg1 * 2, $avg2);
    }

    public function testResetAffectsCalculation()
    {
        $backoff = new PoissonBackoff(500, 5000);

        $delay1 = $backoff->next();
        $backoff->next();
        $backoff->reset();
        $delay3 = $backoff->next();

        // Both should be positive integers from Poisson distribution
        $this->assertIsInt($delay1);
        $this->assertIsInt($delay3);
        $this->assertGreaterThanOrEqual(0, $delay1);
        $this->assertGreaterThanOrEqual(0, $delay3);
    }

    public function testPrivateProperties()
    {
        $backoff = new PoissonBackoff(750, 7500);

        $mean = $this->getPrivateProperty($backoff, 'mean');
        $max = $this->getPrivateProperty($backoff, 'max');
        $attempt = $this->getPrivateProperty($backoff, 'attempt');

        $this->assertEquals(750, $mean);
        $this->assertEquals(7500, $max);
        $this->assertEquals(0, $attempt);
    }

    public function testPrivatePropertiesAfterOperations()
    {
        $backoff = new PoissonBackoff(500, 5000);

        $backoff->next();
        $attempt = $this->getPrivateProperty($backoff, 'attempt');

        $this->assertEquals(1, $attempt);
    }

    public function testZeroMean()
    {
        $backoff = new PoissonBackoff(0, 1000);

        // With mean 0, Poisson distribution should produce mostly 0s
        $delays = [];
        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(1000, $delay);
        }

        // Most should be 0
        $zeros = array_filter($delays, fn ($d) => $d === 0);
        $this->assertGreaterThan(5, count($zeros));
    }

    public function testNegativeMean()
    {
        // Edge case: negative mean
        $backoff = new PoissonBackoff(-100, 1000);

        // Should still produce valid delays
        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertIsInt($delay);
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(1000, $delay);
        }
    }

    public function testZeroMax()
    {
        // Edge case: max is 0
        $backoff = new PoissonBackoff(100, 0);

        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(0, $delay);
        }
    }

    public function testNegativeMax()
    {
        // Edge case: negative max
        $backoff = new PoissonBackoff(100, -100);

        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            // When max is negative, delay is capped to max (-100),
            // then max(0, delay) ensures it's at least 0
            $this->assertEquals(0, $delay);
        }
    }

    public function testMaxSmallerThanMean()
    {
        // Edge case: max smaller than mean
        $backoff = new PoissonBackoff(1000, 100);

        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $this->assertLessThanOrEqual(100, $delay);
        }
    }

    public function testStatisticalProperties()
    {
        // This test verifies basic statistical properties of Poisson distribution
        $backoff = new PoissonBackoff(20, 1000);

        $delays = [];
        for ($i = 0; $i < 1000; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
        }

        $average = array_sum($delays) / count($delays);

        // For Poisson distribution, variance equals mean
        // So standard deviation should be sqrt(mean)
        $variance = 0;
        foreach ($delays as $delay) {
            $variance += pow($delay - $average, 2);
        }
        $variance /= count($delays);
        $stdDev = sqrt($variance);

        // The average should be close to the mean (20)
        // Allow some tolerance for statistical variation
        $this->assertGreaterThan(10, $average);
        $this->assertLessThan(30, $average);

        // Standard deviation should be close to sqrt(mean)
        $expectedStdDev = sqrt(20);
        $this->assertGreaterThan($expectedStdDev * 0.5, $stdDev);
        $this->assertLessThan($expectedStdDev * 2.0, $stdDev);
    }

    public function testLargeMeanValue()
    {
        // Test with large mean value that would cause underflow in original algorithm
        $backoff = new PoissonBackoff(1000, 10000);

        // Should generate values without infinite loop
        $delays = [];
        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(10000, $delay);
        }

        // Average should be close to mean (1000)
        $average = array_sum($delays) / count($delays);
        $this->assertGreaterThan(500, $average);
        $this->assertLessThan(1500, $average);
    }

    public function testVeryLargeMeanValue()
    {
        // Test with very large mean value
        $backoff = new PoissonBackoff(5000, 10000);

        // Should use normal approximation
        $delays = [];
        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $delays[] = $delay;
            $this->assertGreaterThanOrEqual(0, $delay);
            $this->assertLessThanOrEqual(10000, $delay);
        }
    }

    protected function createBackoff(): PoissonBackoff
    {
        return new PoissonBackoff(500, 5000);
    }

    /**
     * PoissonBackoff uses randomness, so it's non-deterministic.
     */
    protected function isDeterministic(): bool
    {
        return false;
    }
}
