<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Backoff;

use InvalidArgumentException;

/**
 * Array-based backoff implementation for retry mechanisms.
 *
 * This class provides a flexible backoff strategy where the delay between
 * retry attempts is determined by a predefined array of time intervals.
 * The intervals are used in sequence, and when the end of the array is reached,
 * it can either stop or continue using the last value.
 *
 * This is useful when you want custom retry patterns that don't follow
 * mathematical formulas like exponential or linear growth.
 */
class ArrayBackoff extends AbstractBackoff
{
    /**
     * Array of delay intervals in milliseconds.
     *
     * @var int[]
     */
    private array $delays;

    /**
     * Whether to continue using the last delay value after exhausting the array.
     * When true, after reaching the end of the delays array, subsequent calls
     * to next() will return the last delay value. When false, next() will return 0.
     */
    private bool $useLastValue;

    /**
     * Constructor to initialize the array-based backoff strategy.
     *
     * @param positive-int[] $delays Array of delays in milliseconds for each retry attempt
     * @param bool $useLastValue Whether to use the last delay value after exhausting the array (default: true)
     * @throws InvalidArgumentException If delays array is empty or contains invalid values
     */
    public function __construct(array $delays, bool $useLastValue = true)
    {
        if (empty($delays)) {
            throw new InvalidArgumentException('Delays array cannot be empty');
        }

        // Validate and sanitize all delay values
        $this->delays = [];
        foreach ($delays as $delay) {
            if (! is_int($delay)) {
                throw new InvalidArgumentException('All delay values must be integers');
            }
            $this->delays[] = $this->ensureNonNegative($delay);
        }

        $this->useLastValue = $useLastValue;
    }

    /**
     * Calculate the delay for the next retry attempt.
     *
     * The delay is selected from the predefined array based on the current attempt count.
     * If the attempt count exceeds the array bounds:
     * - When useLastValue is true: returns the last delay value
     * - When useLastValue is false: returns 0 (no delay)
     *
     * @return int The delay in milliseconds before the next retry attempt
     */
    public function next(): int
    {
        $attempt = $this->getAttempt();
        $delay = 0;

        if ($attempt < count($this->delays)) {
            // Use the delay from the array at the current attempt index
            $delay = $this->delays[$attempt];
        } elseif ($this->useLastValue && ! empty($this->delays)) {
            // Use the last delay value when we've exhausted the array
            $delay = $this->delays[count($this->delays) - 1];
        }

        $this->incrementAttempt();

        return $delay;
    }

    /**
     * Get the array of delays.
     *
     * @return int[] Array of delay intervals in milliseconds
     */
    public function getDelays(): array
    {
        return $this->delays;
    }

    /**
     * Check if the backoff will use the last value after exhausting the array.
     *
     * @return bool True if last value will be used, false otherwise
     */
    public function isUsingLastValue(): bool
    {
        return $this->useLastValue;
    }

    /**
     * Create an ArrayBackoff instance from a comma-separated string of delays.
     *
     * @param string $delays Comma-separated delays in milliseconds (e.g., "100,500,1000,2000")
     * @param bool $useLastValue Whether to use the last delay value after exhausting the array
     * @throws InvalidArgumentException If the string contains invalid values
     */
    public static function fromString(string $delays, bool $useLastValue = true): static
    {
        $delayArray = array_map('trim', explode(',', $delays));
        $intDelays = [];

        foreach ($delayArray as $delay) {
            if (! is_numeric($delay)) {
                throw new InvalidArgumentException("Invalid delay value: '{$delay}'. Must be numeric.");
            }
            $intDelays[] = (int) $delay;
        }

        return new static($intDelays, $useLastValue);
    }

    /**
     * Create an ArrayBackoff instance with a common retry pattern.
     *
     * @param string $pattern Pattern type: 'short', 'medium', 'long', or 'exponential'
     * @throws InvalidArgumentException If pattern is not recognized
     */
    public static function fromPattern(string $pattern): static
    {
        return match ($pattern) {
            'short' => new static([100, 200, 300, 500, 1000]),
            'medium' => new static([200, 500, 1000, 2000, 5000]),
            'long' => new static([500, 1000, 2000, 5000, 10000, 30000]),
            'exponential' => new static([100, 200, 400, 800, 1600, 3200, 6400]),
            default => throw new InvalidArgumentException("Unknown pattern: '{$pattern}'. Use 'short', 'medium', 'long', or 'exponential'."),
        };
    }
}
