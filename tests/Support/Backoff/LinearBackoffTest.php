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

use FriendsOfHyperf\Support\Backoff\LinearBackoff;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class LinearBackoffTest extends BackoffTestCase
{
    public function testConstructorWithDefaults()
    {
        $backoff = new LinearBackoff();
        $delay = $backoff->next();
        $this->assertEquals(100, $delay); // initial delay
    }

    public function testLinearGrowth()
    {
        $backoff = new LinearBackoff(100, 50, 1000);

        // Formula: initial + (attempt * step)
        // attempt starts from 0, then increments
        $this->assertEquals(100 + (0 * 50), $backoff->next()); // attempt 1
        $this->assertEquals(100 + (1 * 50), $backoff->next()); // attempt 2
        $this->assertEquals(100 + (2 * 50), $backoff->next()); // attempt 3
        $this->assertEquals(100 + (3 * 50), $backoff->next()); // attempt 4
    }

    public function testMaximumDelayCap()
    {
        $backoff = new LinearBackoff(100, 100, 500);

        // Without cap: 100, 200, 300, 400, 500, 600, 700...
        // With cap at 500: 100, 200, 300, 400, 500, 500, 500...
        $this->assertEquals(100, $backoff->next());
        $this->assertEquals(200, $backoff->next());
        $this->assertEquals(300, $backoff->next());
        $this->assertEquals(400, $backoff->next());
        $this->assertEquals(500, $backoff->next());
        $this->assertEquals(500, $backoff->next()); // Capped
        $this->assertEquals(500, $backoff->next()); // Capped
    }

    public function testCustomParameters()
    {
        $backoff = new LinearBackoff(50, 25, 300);

        // Formula: 50 + (attempt * 25)
        $this->assertEquals(50 + (0 * 25), $backoff->next());
        $this->assertEquals(50 + (1 * 25), $backoff->next());
        $this->assertEquals(50 + (2 * 25), $backoff->next());
        $this->assertEquals(50 + (3 * 25), $backoff->next());
        $this->assertEquals(50 + (4 * 25), $backoff->next()); // 150
        $this->assertEquals(50 + (5 * 25), $backoff->next()); // 175
        $this->assertEquals(50 + (6 * 25), $backoff->next()); // 200
        $this->assertEquals(50 + (7 * 25), $backoff->next()); // 225
        $this->assertEquals(50 + (8 * 25), $backoff->next()); // 250
        $this->assertEquals(50 + (9 * 25), $backoff->next()); // 275
        $this->assertEquals(50 + (10 * 25), $backoff->next()); // 300 (max)
        $this->assertEquals(300, $backoff->next()); // Capped at max
    }

    public function testZeroStep()
    {
        $backoff = new LinearBackoff(100, 0, 500);

        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(100, $delay); // Always the initial value
        }
    }

    public function testNegativeStep()
    {
        $backoff = new LinearBackoff(100, -10, 500);

        $this->assertEquals(100, $backoff->next());
        $this->assertEquals(90, $backoff->next());
        $this->assertEquals(80, $backoff->next());
        $this->assertEquals(70, $backoff->next());
    }

    public function testResetAffectsCalculation()
    {
        $backoff = new LinearBackoff(200, 100, 1000);

        $delay1 = $backoff->next(); // 200
        $delay2 = $backoff->next(); // 300
        $backoff->reset();
        $delay3 = $backoff->next(); // 200 again

        $this->assertEquals(200, $delay1);
        $this->assertEquals(300, $delay2);
        $this->assertEquals(200, $delay3);
    }

    public function testPrivateProperties()
    {
        $backoff = new LinearBackoff(150, 75, 750);

        $initial = $this->getPrivateProperty($backoff, 'initial');
        $step = $this->getPrivateProperty($backoff, 'step');
        $max = $this->getPrivateProperty($backoff, 'max');

        $this->assertEquals(150, $initial);
        $this->assertEquals(75, $step);
        $this->assertEquals(750, $max);
    }

    public function testMaxSmallerThanInitial()
    {
        // Edge case: max is smaller than initial
        $backoff = new LinearBackoff(500, 100, 300);

        // Should always return max (300) since it's smaller than initial
        for ($i = 0; $i < 5; ++$i) {
            $delay = $backoff->next();
            $this->assertEquals(300, $delay);
        }
    }

    protected function createBackoff(): LinearBackoff
    {
        return new LinearBackoff(100, 50, 1000);
    }
}
