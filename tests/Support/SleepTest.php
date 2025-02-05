<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use FriendsOfHyperf\Support\Sleep;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Hyperf\Support\now;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
class SleepTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Sleep::fake(false);

        Carbon::setTestNow();
    }

    public function testItSleepsForSeconds()
    {
        $start = microtime(true);
        Sleep::for(1)->seconds();
        $end = microtime(true);

        $this->assertEqualsWithDelta(1, $end - $start, 0.03);
    }

    public function testItSleepsForSecondsWithMilliseconds()
    {
        $start = microtime(true);
        Sleep::for(1.5)->seconds();
        $end = microtime(true);

        $this->assertEqualsWithDelta(1.5, $end - $start, 0.03);
    }

    public function testItCanFakeSleeping()
    {
        Sleep::fake();

        $start = microtime(true);
        Sleep::for(1.5)->seconds();
        $end = microtime(true);

        $this->assertEqualsWithDelta(0, $end - $start, 0.03);
    }

    public function testItCanSpecifyMinutes()
    {
        Sleep::fake();

        $sleep = Sleep::for(1.5)->minutes();

        $this->assertSame($sleep->duration->totalMicroseconds, 90_000_000);
    }

    public function testItCanSpecifyMinute()
    {
        Sleep::fake();

        $sleep = Sleep::for(1)->minute();

        $this->assertSame($sleep->duration->totalMicroseconds, 60_000_000);
    }

    public function testItCanSpecifySeconds()
    {
        Sleep::fake();

        $sleep = Sleep::for(1.5)->seconds();

        $this->assertSame($sleep->duration->totalMicroseconds, 1_500_000);
    }

    public function testItCanSpecifySecond()
    {
        Sleep::fake();

        $sleep = Sleep::for(1)->second();

        $this->assertSame($sleep->duration->totalMicroseconds, 1_000_000);
    }

    public function testItCanSpecifyMilliseconds()
    {
        Sleep::fake();

        $sleep = Sleep::for(1.5)->milliseconds();

        $this->assertSame($sleep->duration->totalMicroseconds, 1_500);
    }

    public function testItCanSpecifyMillisecond()
    {
        Sleep::fake();

        $sleep = Sleep::for(1)->millisecond();

        $this->assertSame($sleep->duration->totalMicroseconds, 1_000);
    }

    public function testItCanSpecifyMicroseconds()
    {
        Sleep::fake();

        $sleep = Sleep::for(1.5)->microseconds();

        // rounded as microseconds is the smallest unit supported...
        $this->assertSame($sleep->duration->totalMicroseconds, 1);
    }

    public function testItCanSpecifyMicrosecond()
    {
        Sleep::fake();

        $sleep = Sleep::for(1)->microsecond();

        $this->assertSame($sleep->duration->totalMicroseconds, 1);
    }

    public function testItCanChainDurations()
    {
        Sleep::fake();

        $sleep = Sleep::for(1)->second()
            ->and(500)->microseconds();

        $this->assertSame($sleep->duration->totalMicroseconds, 1000500);
    }

    public function testItCanUseDateInterval()
    {
        Sleep::fake();

        $sleep = Sleep::for(CarbonInterval::seconds(1)->addMilliseconds(5));

        $this->assertSame($sleep->duration->totalMicroseconds, 1_005_000);
    }

    public function testItThrowsForUnknownTimeUnit()
    {
        try {
            Sleep::for(5);
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertSame('Unknown duration unit.', $e->getMessage());
        }
    }

    public function testItCanAssertSequence()
    {
        Sleep::fake();

        Sleep::for(5)->seconds();
        Sleep::for(1)->seconds()->and(5)->microsecond();

        Sleep::assertSequence([
            Sleep::for(5)->seconds(),
            Sleep::for(1)->seconds()->and(5)->microsecond(),
        ]);
    }

    public function testItFailsSequenceAssertion()
    {
        Sleep::fake();

        Sleep::for(5)->seconds();
        Sleep::for(1)->seconds()->and(5)->microseconds();

        try {
            Sleep::assertSequence([
                Sleep::for(5)->seconds(),
                Sleep::for(9)->seconds()->and(8)->milliseconds(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected sleep duration of [9 seconds 8 milliseconds] but actually slept for [1 second 5 microseconds].\nFailed asserting that false is true.", $e->getMessage());
        }
    }

    public function testItCanUseSleep()
    {
        Sleep::fake();

        Sleep::sleep(3);

        Sleep::assertSequence([
            Sleep::for(3)->seconds(),
        ]);
    }

    public function testItCanUseUSleep()
    {
        Sleep::fake();

        Sleep::usleep(3);

        Sleep::assertSequence([
            Sleep::for(3)->microseconds(),
        ]);
    }

    public function testItCanSleepTillGivenTime()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(now()->addMinute());

        Sleep::assertSequence([
            Sleep::for(60)->seconds(),
        ]);
    }

    public function testItCanSleepTillGivenTimestamp()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(now()->addMinute()->timestamp);

        Sleep::assertSequence([
            Sleep::for(60)->seconds(),
        ]);
    }

    public function testItCanSleepTillGivenTimestampAsString()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(strval(now()->addMinute()->timestamp));

        Sleep::assertSequence([
            Sleep::for(60)->seconds(),
        ]);
    }

    public function testItCanSleepTillGivenTimestampAsStringWithMilliseconds()
    {
        Sleep::fake();
        Carbon::setTestNow('2000-01-01 00:00:00.000'); // 946684800

        Sleep::until('946684899.123');

        Sleep::assertSequence([
            Sleep::for(1)->minute()
                ->and(39)->seconds()
                ->and(123)->milliseconds(),
        ]);
    }

    public function testItSleepsForZeroTimeWithNegativeDateTime()
    {
        Sleep::fake();
        Carbon::setTestNow(now()->startOfDay());

        Sleep::until(now()->subMinutes(100));

        Sleep::assertSequence([
            Sleep::for(0)->seconds(),
        ]);
    }

    public function testSleepingForZeroTime()
    {
        Sleep::fake();

        Sleep::for(0)->seconds();

        try {
            Sleep::assertSequence([
                Sleep::for(1)->seconds(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected sleep duration of [1 second] but actually slept for [0 microseconds].\nFailed asserting that false is true.", $e->getMessage());
        }
    }

    public function testItFailsWhenSequenceContainsTooManySleeps()
    {
        Sleep::fake();

        Sleep::for(1)->seconds();

        try {
            Sleep::assertSequence([
                Sleep::for(1)->seconds(),
                Sleep::for(1)->seconds(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [2] sleeps but found [1].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }
    }

    public function testSilentlySetsDurationToZeroForNegativeValues()
    {
        Sleep::fake();

        Sleep::for(-1)->seconds();

        Sleep::assertSequence([
            Sleep::for(0)->seconds(),
        ]);
    }

    public function testItDoesntCaptureAssertionInstances()
    {
        Sleep::fake();

        Sleep::for(1)->second();

        Sleep::assertSequence([
            Sleep::for(1)->second(),
        ]);

        try {
            Sleep::assertSequence([
                Sleep::for(1)->second(),
                Sleep::for(1)->second(),
            ]);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [2] sleeps but found [1].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }
    }

    public function testAssertNeverSlept()
    {
        Sleep::fake();

        Sleep::assertNeverSlept();

        Sleep::for(1)->seconds();

        try {
            Sleep::assertNeverSlept();
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [0] sleeps but found [1].\nFailed asserting that 1 is identical to 0.", $e->getMessage());
        }
    }

    public function testAssertNeverAgainstZeroSecondSleep()
    {
        Sleep::fake();

        Sleep::assertNeverSlept();

        Sleep::for(0)->seconds();

        try {
            Sleep::assertNeverSlept();
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [0] sleeps but found [1].\nFailed asserting that 1 is identical to 0.", $e->getMessage());
        }
    }

    public function testItCanAssertNoSleepingOccurred()
    {
        Sleep::fake();

        Sleep::assertInsomniac();

        Sleep::for(0)->second();

        // we still have not slept...
        Sleep::assertInsomniac();

        Sleep::for(1)->second();

        try {
            Sleep::assertInsomniac();
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Unexpected sleep duration of [1 second] found.\nFailed asserting that 1000000 is identical to 0.", $e->getMessage());
        }
    }

    public function testItCanAssertSleepCount()
    {
        Sleep::fake();

        Sleep::assertSleptTimes(0);

        Sleep::for(1)->second();

        Sleep::assertSleptTimes(1);

        try {
            Sleep::assertSleptTimes(0);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [0] sleeps but found [1].\nFailed asserting that 1 is identical to 0.", $e->getMessage());
        }

        try {
            Sleep::assertSleptTimes(2);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("Expected [2] sleeps but found [1].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }
    }

    public function testAssertSlept()
    {
        Sleep::fake();

        Sleep::assertSlept(fn () => true, 0);

        try {
            Sleep::assertSlept(fn () => true);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("The expected sleep was found [0] times instead of [1].\nFailed asserting that 0 is identical to 1.", $e->getMessage());
        }

        Sleep::for(5)->seconds();

        Sleep::assertSlept(fn (CarbonInterval $duration) => $duration->totalSeconds === 5);

        try {
            Sleep::assertSlept(fn (CarbonInterval $duration) => $duration->totalSeconds === 5, 2);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("The expected sleep was found [1] times instead of [2].\nFailed asserting that 1 is identical to 2.", $e->getMessage());
        }

        try {
            Sleep::assertSlept(fn (CarbonInterval $duration) => $duration->totalSeconds === 6);
            $this->fail();
        } catch (AssertionFailedError $e) {
            $this->assertSame("The expected sleep was found [0] times instead of [1].\nFailed asserting that 0 is identical to 1.", $e->getMessage());
        }
    }

    public function testItCanCreateMacrosViaMacroable()
    {
        Sleep::fake();

        Sleep::macro('forSomeConfiguredAmountOfTime', static function () {
            return Sleep::for(3)->seconds();
        });

        Sleep::macro('useSomeOtherAmountOfTime', function () {
            /* @phpstan-ignore-next-line */
            return $this->duration(1.234)->seconds();
        });

        Sleep::macro('andSomeMoreGranularControl', function () {
            /* @phpstan-ignore-next-line */
            return $this->and(567)->microseconds();
        });

        // A static macro can be referenced
        $sleep = Sleep::forSomeConfiguredAmountOfTime();
        $this->assertSame($sleep->duration->totalMicroseconds, 3000000);

        // A macro can specify a new duration
        $sleep = $sleep->useSomeOtherAmountOfTime();
        $this->assertSame($sleep->duration->totalMicroseconds, 1234000);

        // A macro can supplement an existing duration
        $sleep = $sleep->andSomeMoreGranularControl();
        $this->assertSame($sleep->duration->totalMicroseconds, 1234567);
    }

    public function testItCanReplacePreviouslyDefinedDurations()
    {
        Sleep::fake();

        Sleep::macro('setDuration', function ($duration) {
            /* @phpstan-ignore-next-line */
            return $this->duration($duration);
        });

        $sleep = Sleep::for(1)->second();
        $this->assertSame($sleep->duration->totalMicroseconds, 1000000);

        $sleep->setDuration(2)->second();
        $this->assertSame($sleep->duration->totalMicroseconds, 2000000);

        $sleep->setDuration(500)->milliseconds();
        $this->assertSame($sleep->duration->totalMicroseconds, 500000);
    }

    public function testItCanSleepConditionallyWhen()
    {
        Sleep::fake();

        // Control test
        Sleep::assertSlept(fn () => true, 0);
        Sleep::for(1)->second();
        Sleep::assertSlept(fn () => true, 1);
        Sleep::fake();
        Sleep::assertSlept(fn () => true, 0);

        // Reset
        Sleep::fake();

        // Will not sleep if `when()` yields `false`
        Sleep::for(1)->second()->when(false);
        Sleep::for(1)->second()->when(fn () => false);

        // Will not sleep if `unless()` yields `true`
        Sleep::for(1)->second()->unless(true);
        Sleep::for(1)->second()->unless(fn () => true);

        // Finish 'do not sleep' tests - assert no sleeping occurred
        Sleep::assertSlept(fn () => true, 0);

        // Will sleep if `when()` yields `true`
        Sleep::for(1)->second()->when(true);
        Sleep::assertSlept(fn () => true, 1);
        Sleep::for(1)->second()->when(fn () => true);
        Sleep::assertSlept(fn () => true, 2);

        // Will sleep if `unless()` yields `false`
        Sleep::for(1)->second()->unless(false);
        Sleep::assertSlept(fn () => true, 3);
        Sleep::for(1)->second()->unless(fn () => false);
        Sleep::assertSlept(fn () => true, 4);
    }

    public function testItCanRegisterCallbacksToRunInTests()
    {
        $countA = 0;
        $countB = 0;
        Sleep::fake();
        Sleep::whenFakingSleep(function ($duration) use (&$countA) {
            $countA += $duration->totalMilliseconds;
        });
        Sleep::whenFakingSleep(function ($duration) use (&$countB) {
            $countB += $duration->totalMilliseconds;
        });

        Sleep::for(1)->millisecond();
        Sleep::for(2)->millisecond();

        Sleep::assertSequence([
            Sleep::for(1)->millisecond(),
            Sleep::for(2)->millisecond(),
        ]);

        $this->assertSame(3, $countA);
        $this->assertSame(3, $countB);
    }

    public function testItDoesntRunCallbacksWhenNotFaking()
    {
        Sleep::whenFakingSleep(function () {
            throw new Exception('Should not run without faking.');
        });

        Sleep::for(1)->millisecond();

        $this->assertTrue(true);
    }
}
