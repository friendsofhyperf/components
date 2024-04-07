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

use PHPUnit\Framework\TestCase;
use RuntimeException;

use function FriendsOfHyperf\Support\retry;

/**
 * @internal
 * @coversNothing
 */
class FunctionsTest extends TestCase
{
    public function testRetry()
    {
        $startTime = microtime(true);

        $attempts = retry(2, function ($attempts) {
            if ($attempts > 1) {
                return $attempts;
            }

            throw new RuntimeException();
        }, 100);

        // Make sure we made two attempts
        $this->assertEquals(2, $attempts);

        // Make sure we waited 100ms for the first attempt
        $this->assertEqualsWithDelta(0.1, microtime(true) - $startTime, 0.03);
    }

    public function testRetryWithPassingSleepCallback()
    {
        $startTime = microtime(true);

        $attempts = retry(3, function ($attempts) {
            if ($attempts > 2) {
                return $attempts;
            }

            throw new RuntimeException();
        }, function ($attempt, $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception);

            return $attempt * 100;
        });

        // Make sure we made three attempts
        $this->assertEquals(3, $attempts);

        // Make sure we waited 300ms for the first two attempts
        $this->assertEqualsWithDelta(0.3, microtime(true) - $startTime, 0.03);
    }

    public function testRetryWithPassingWhenCallback()
    {
        $startTime = microtime(true);

        $attempts = retry(2, function ($attempts) {
            if ($attempts > 1) {
                return $attempts;
            }

            throw new RuntimeException();
        }, 100, function ($ex) {
            return true;
        });

        // Make sure we made two attempts
        $this->assertEquals(2, $attempts);

        // Make sure we waited 100ms for the first attempt
        $this->assertEqualsWithDelta(0.1, microtime(true) - $startTime, 0.03);
    }

    public function testRetryWithFailingWhenCallback()
    {
        $this->expectException(RuntimeException::class);

        retry(2, function ($attempts) {
            if ($attempts > 1) {
                return $attempts;
            }

            throw new RuntimeException();
        }, 100, function ($ex) {
            return false;
        });
    }

    public function testRetryWithBackoff()
    {
        $startTime = microtime(true);
        $attempts = retry([50, 100, 200], function ($attempts) {
            if ($attempts > 3) {
                return $attempts;
            }

            throw new RuntimeException();
        });

        // Make sure we made four attempts
        $this->assertEquals(4, $attempts);

        $this->assertEqualsWithDelta(0.05 + 0.1 + 0.2, microtime(true) - $startTime, 0.05);
    }
}
