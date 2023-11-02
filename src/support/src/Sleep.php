<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Closure;
use DateInterval;
use DateTimeInterface;
use Hyperf\Macroable\Macroable;
use PHPUnit\Framework\Assert as PHPUnit;
use RuntimeException;

use function Hyperf\Collection\collect;
use function Hyperf\Support\value;
use function Hyperf\Tappable\tap;

class Sleep
{
    use Macroable;

    /**
     * The fake sleep callbacks.
     *
     * @var array
     */
    public static $fakeSleepCallbacks = [];

    /**
     * The total duration to sleep.
     *
     * @var \Carbon\CarbonInterval
     */
    public $duration;

    /**
     * The pending duration to sleep.
     *
     * @var float|int|null
     */
    protected $pending;

    /**
     * Indicates that all sleeping should be faked.
     *
     * @var bool
     */
    protected static $fake = false;

    /**
     * The sequence of sleep durations encountered while faking.
     *
     * @var array<int, \Carbon\CarbonInterval>
     */
    protected static $sequence = [];

    /**
     * Indicates if the instance should sleep.
     *
     * @var bool
     */
    protected $shouldSleep = true;

    /**
     * Create a new class instance.
     *
     * @param DateInterval|float|int $duration
     */
    public function __construct($duration)
    {
        $this->duration($duration);
    }

    /**
     * Handle the object's destruction.
     */
    public function __destruct()
    {
        if (! $this->shouldSleep) {
            return;
        }

        if ($this->pending !== null) {
            throw new RuntimeException('Unknown duration unit.');
        }

        if (static::$fake) {
            static::$sequence[] = $this->duration;

            foreach (static::$fakeSleepCallbacks as $callback) {
                $callback($this->duration);
            }

            return;
        }

        $remaining = $this->duration->copy();

        $seconds = (int) $remaining->totalSeconds;

        if ($seconds > 0) {
            sleep($seconds);

            $remaining = $remaining->subSeconds($seconds);
        }

        $microseconds = (int) $remaining->totalMicroseconds;

        if ($microseconds > 0) {
            usleep($microseconds);
        }
    }

    /**
     * Sleep for the given duration.
     *
     * @param DateInterval|float|int $duration
     * @return static
     */
    public static function for($duration)
    {
        return new static($duration);
    }

    /**
     * Sleep until the given timestamp.
     *
     * @param DateTimeInterface|int|float|numeric-string $timestamp
     * @return static
     */
    public static function until($timestamp)
    {
        if (is_numeric($timestamp)) {
            $timestamp = Carbon::createFromTimestamp($timestamp);
        }

        return new static(Carbon::now()->diff($timestamp));
    }

    /**
     * Sleep for the given number of microseconds.
     *
     * @param int $duration
     * @return static
     */
    public static function usleep($duration)
    {
        return (new static($duration))->microseconds();
    }

    /**
     * Sleep for the given number of seconds.
     *
     * @param float|int $duration
     * @return static
     */
    public static function sleep($duration)
    {
        return (new static($duration))->seconds();
    }

    /**
     * Sleep for the given number of minutes.
     *
     * @return $this
     */
    public function minutes()
    {
        $this->duration->add('minutes', $this->pullPending());

        return $this;
    }

    /**
     * Sleep for one minute.
     *
     * @return $this
     */
    public function minute()
    {
        return $this->minutes();
    }

    /**
     * Sleep for the given number of seconds.
     *
     * @return $this
     */
    public function seconds()
    {
        $this->duration->add('seconds', $this->pullPending());

        return $this;
    }

    /**
     * Sleep for one second.
     *
     * @return $this
     */
    public function second()
    {
        return $this->seconds();
    }

    /**
     * Sleep for the given number of milliseconds.
     *
     * @return $this
     */
    public function milliseconds()
    {
        $this->duration->add('milliseconds', $this->pullPending());

        return $this;
    }

    /**
     * Sleep for one millisecond.
     *
     * @return $this
     */
    public function millisecond()
    {
        return $this->milliseconds();
    }

    /**
     * Sleep for the given number of microseconds.
     *
     * @return $this
     */
    public function microseconds()
    {
        $this->duration->add('microseconds', $this->pullPending());

        return $this;
    }

    /**
     * Sleep for on microsecond.
     *
     * @return $this
     */
    public function microsecond()
    {
        return $this->microseconds();
    }

    /**
     * Add additional time to sleep for.
     *
     * @param float|int $duration
     * @return $this
     */
    public function and($duration)
    {
        $this->pending = $duration;

        return $this;
    }

    /**
     * Stay awake and capture any attempts to sleep.
     *
     * @param bool $value
     */
    public static function fake($value = true)
    {
        static::$fake = $value;

        static::$sequence = [];
        static::$fakeSleepCallbacks = [];
    }

    /**
     * Assert a given amount of sleeping occurred a specific number of times.
     *
     * @param Closure $expected
     * @param int $times
     */
    public static function assertSlept($expected, $times = 1)
    {
        $count = collect(static::$sequence)->filter($expected)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            "The expected sleep was found [{$count}] times instead of [{$times}]."
        );
    }

    /**
     * Assert sleeping occurred a given number of times.
     *
     * @param int $expected
     */
    public static function assertSleptTimes($expected)
    {
        PHPUnit::assertSame($expected, $count = count(static::$sequence), "Expected [{$expected}] sleeps but found [{$count}].");
    }

    /**
     * Assert the given sleep sequence was encountered.
     *
     * @param array $sequence
     */
    public static function assertSequence($sequence)
    {
        static::assertSleptTimes(count($sequence));

        collect($sequence)
            ->zip(static::$sequence)
            ->eachSpread(function (?Sleep $expected, CarbonInterval $actual) {
                if ($expected === null) {
                    return;
                }

                PHPUnit::assertTrue(
                    $expected->shouldNotSleep()->duration->equalTo($actual),
                    vsprintf('Expected sleep duration of [%s] but actually slept for [%s].', [
                        $expected->duration->cascade()->forHumans([
                            'options' => 0,
                            'minimumUnit' => 'microsecond',
                        ]),
                        $actual->cascade()->forHumans([
                            'options' => 0,
                            'minimumUnit' => 'microsecond',
                        ]),
                    ])
                );
            });
    }

    /**
     * Assert that no sleeping occurred.
     */
    public static function assertNeverSlept()
    {
        return static::assertSleptTimes(0);
    }

    /**
     * Assert that no sleeping occurred.
     */
    public static function assertInsomniac()
    {
        if (static::$sequence === []) {
            PHPUnit::assertTrue(true);
        }

        foreach (static::$sequence as $duration) {
            PHPUnit::assertSame(0, $duration->totalMicroseconds, vsprintf('Unexpected sleep duration of [%s] found.', [
                $duration->cascade()->forHumans([
                    'options' => 0,
                    'minimumUnit' => 'microsecond',
                ]),
            ]));
        }
    }

    /**
     * Only sleep when the given condition is true.
     *
     * @param (Closure($this): bool)|bool $condition
     * @param mixed $condition
     * @return $this
     */
    public function when($condition)
    {
        $this->shouldSleep = (bool) value($condition, $this);

        return $this;
    }

    /**
     * Don't sleep when the given condition is true.
     *
     * @param (Closure($this): bool)|bool $condition
     * @param mixed $condition
     * @return $this
     */
    public function unless($condition)
    {
        return $this->when(! value($condition, $this));
    }

    /**
     * Specify a callback that should be invoked when faking sleep within a test.
     *
     * @param callable $callback
     */
    public static function whenFakingSleep($callback)
    {
        static::$fakeSleepCallbacks[] = $callback;
    }

    /**
     * Sleep for the given duration. Replaces any previously defined duration.
     *
     * @param DateInterval|float|int $duration
     * @return $this
     */
    protected function duration($duration)
    {
        if (! $duration instanceof DateInterval) {
            $this->duration = CarbonInterval::microsecond(0);

            $this->pending = $duration;
        } else {
            $duration = CarbonInterval::instance($duration);

            if ($duration->totalMicroseconds < 0) {
                $duration = CarbonInterval::seconds(0);
            }

            $this->duration = $duration;
            $this->pending = null;
        }

        return $this;
    }

    /**
     * Resolve the pending duration.
     *
     * @return float|int
     */
    protected function pullPending()
    {
        if ($this->pending === null) {
            $this->shouldNotSleep();

            throw new RuntimeException('No duration specified.');
        }

        if ($this->pending < 0) {
            $this->pending = 0;
        }

        return tap($this->pending, function () {
            $this->pending = null;
        });
    }

    /**
     * Indicate that the instance should not sleep.
     *
     * @return $this
     */
    protected function shouldNotSleep()
    {
        $this->shouldSleep = false;

        return $this;
    }
}
