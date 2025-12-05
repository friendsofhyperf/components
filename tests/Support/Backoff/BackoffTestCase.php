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

use FriendsOfHyperf\Support\Backoff\BackoffInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('support')]
abstract class BackoffTestCase extends TestCase
{
    public function testImplementsBackoffInterface()
    {
        $backoff = $this->createBackoff();
        $this->assertInstanceOf(BackoffInterface::class, $backoff);
    }

    public function testNextReturnsPositiveInteger()
    {
        $backoff = $this->createBackoff();

        for ($i = 0; $i < 10; ++$i) {
            $delay = $backoff->next();
            $this->assertIsInt($delay);
            $this->assertGreaterThanOrEqual(0, $delay);
        }
    }

    public function testGetAttempt()
    {
        $backoff = $this->createBackoff();

        $this->assertEquals(0, $backoff->getAttempt());

        $backoff->next();
        $this->assertEquals(1, $backoff->getAttempt());

        $backoff->next();
        $this->assertEquals(2, $backoff->getAttempt());
    }

    public function testReset()
    {
        $backoff = $this->createBackoff();

        // Make some attempts
        $backoff->next();
        $backoff->next();
        $this->assertGreaterThan(0, $backoff->getAttempt());

        // Reset and verify
        $backoff->reset();
        $this->assertEquals(0, $backoff->getAttempt());
    }

    public function testResetAffectsNextCalculation()
    {
        $backoff = $this->createBackoff();

        // Get first delay
        $firstDelay = $backoff->next();

        // Get second delay
        $backoff->next();

        // Reset
        $backoff->reset();

        // Get delay after reset
        $afterResetDelay = $backoff->next();

        // For deterministic strategies, should be same as first
        // For random strategies, just verify we're at attempt 1
        $this->assertEquals(1, $backoff->getAttempt());

        // If this is a deterministic strategy, verify the delay
        if ($this->isDeterministic()) {
            $this->assertEquals($firstDelay, $afterResetDelay);
        }
    }

    /**
     * Override in test classes for random strategies
     */
    protected function isDeterministic(): bool
    {
        return true;
    }

    abstract protected function createBackoff(): BackoffInterface;

    protected function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        return $prop->getValue($object);
    }

    protected function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setValue($object, $value);
    }
}
