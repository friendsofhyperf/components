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

use FriendsOfHyperf\Support\Backoff\ArrayBackoff;
use InvalidArgumentException;

/**
 * @internal
 * @covers \FriendsOfHyperf\Support\Backoff\ArrayBackoff
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class ArrayBackoffTest extends BackoffTestCase
{
    public function testConstructorWithValidDelays()
    {
        $delays = [100, 200, 300, 400];
        $backoff = new ArrayBackoff($delays);

        $this->assertSame($delays, $backoff->getDelays());
        $this->assertTrue($backoff->isUsingLastValue());
    }

    public function testConstructorWithUseLastValueFalse()
    {
        $delays = [100, 200, 300];
        $backoff = new ArrayBackoff($delays, false);

        $this->assertSame($delays, $backoff->getDelays());
        $this->assertFalse($backoff->isUsingLastValue());
    }

    public function testConstructorWithEmptyArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Delays array cannot be empty');
        new ArrayBackoff([]);
    }

    public function testConstructorWithNonIntegerValues()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All delay values must be integers');
        new ArrayBackoff([100, '200', 300]);
    }

    public function testConstructorWithNegativeValues()
    {
        $backoff = new ArrayBackoff([-100, 200, -300]);
        $this->assertSame([0, 200, 0], $backoff->getDelays());
    }

    public function testNextReturnsDelaysInSequence()
    {
        $delays = [100, 200, 300];
        $backoff = new ArrayBackoff($delays);

        $this->assertEquals(100, $backoff->next());
        $this->assertEquals(200, $backoff->next());
        $this->assertEquals(300, $backoff->next());
    }

    public function testNextWithUseLastValueTrue()
    {
        $delays = [100, 200, 300];
        $backoff = new ArrayBackoff($delays, true);

        $this->assertEquals(100, $backoff->next());
        $this->assertEquals(200, $backoff->next());
        $this->assertEquals(300, $backoff->next());
        $this->assertEquals(300, $backoff->next()); // Uses last value
        $this->assertEquals(300, $backoff->next()); // Continues using last value
    }

    public function testNextWithUseLastValueFalse()
    {
        $delays = [100, 200, 300];
        $backoff = new ArrayBackoff($delays, false);

        $this->assertEquals(100, $backoff->next());
        $this->assertEquals(200, $backoff->next());
        $this->assertEquals(300, $backoff->next());
        $this->assertEquals(0, $backoff->next()); // Returns 0 after array is exhausted
        $this->assertEquals(0, $backoff->next()); // Continues returning 0
    }

    public function testFromValidString()
    {
        $backoff = ArrayBackoff::fromString('100, 200, 300, 400');
        $this->assertSame([100, 200, 300, 400], $backoff->getDelays());
    }

    public function testFromStringWithTrailingSpaces()
    {
        $backoff = ArrayBackoff::fromString(' 100 , 200 , 300 ');
        $this->assertSame([100, 200, 300], $backoff->getDelays());
    }

    public function testFromStringWithNonNumericValues()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid delay value: 'abc'. Must be numeric.");
        ArrayBackoff::fromString('100, abc, 300');
    }

    public function testFromPatternShort()
    {
        $backoff = ArrayBackoff::fromPattern('short');
        $this->assertSame([100, 200, 300, 500, 1000], $backoff->getDelays());
    }

    public function testFromPatternMedium()
    {
        $backoff = ArrayBackoff::fromPattern('medium');
        $this->assertSame([200, 500, 1000, 2000, 5000], $backoff->getDelays());
    }

    public function testFromPatternLong()
    {
        $backoff = ArrayBackoff::fromPattern('long');
        $this->assertSame([500, 1000, 2000, 5000, 10000, 30000], $backoff->getDelays());
    }

    public function testFromPatternExponential()
    {
        $backoff = ArrayBackoff::fromPattern('exponential');
        $this->assertSame([100, 200, 400, 800, 1600, 3200, 6400], $backoff->getDelays());
    }

    public function testFromPatternWithInvalidPattern()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown pattern: 'invalid'. Use 'short', 'medium', 'long', or 'exponential'.");
        ArrayBackoff::fromPattern('invalid');
    }

    public function testReset()
    {
        $delays = [100, 200, 300];
        $backoff = new ArrayBackoff($delays);

        // Make some attempts
        $backoff->next();
        $backoff->next();
        $this->assertEquals(2, $backoff->getAttempt());

        // Reset
        $backoff->reset();
        $this->assertEquals(0, $backoff->getAttempt());

        // Should start from beginning
        $this->assertEquals(100, $backoff->next());
    }

    public function testSleepMethod()
    {
        $delays = [1, 2, 3]; // Small delays for testing
        $backoff = new ArrayBackoff($delays);

        $start = microtime(true);
        $actualDelay = $backoff->sleep();
        $end = microtime(true);

        $this->assertEquals(1, $actualDelay);
        $this->assertGreaterThanOrEqual(0.001, $end - $start); // At least 1ms
    }

    protected function createBackoff(): \FriendsOfHyperf\Support\Backoff\BackoffInterface
    {
        return new ArrayBackoff([100, 200, 300, 400]);
    }
}
