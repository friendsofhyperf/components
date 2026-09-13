<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Tests\TestCase;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * Verifies that process-level static state previously shared between requests
 * is now stored in the coroutine scoped Hyperf\Context\Context.
 *
 * Every test method runs inside `Swoole\Coroutine::run()` (see
 * FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine), so Hyperf
 * Context values are isolated per coroutine.
 *
 * @internal
 */
class CoroutineScopedStateTest extends TestCase
{
    public function testTransactionCanBeSetAndClearedWithinCoroutine(): void
    {
        $this->assertGreaterThan(0, Coroutine::getCid());

        Integration::setTransaction('A');
        $this->assertSame('A', Integration::getTransaction());

        Integration::setTransaction(null);
        $this->assertNull(Integration::getTransaction());
    }

    public function testTransactionIsIsolatedBetweenCoroutines(): void
    {
        $values = [];
        $firstSet = new Channel(1);
        $secondChecked = new Channel(1);
        $finished = new Channel(2);

        Coroutine::create(function () use ($firstSet, $secondChecked, $finished, &$values): void {
            Integration::setTransaction('A');
            $firstSet->push(true); // Signal that 'A' has been set.
            $secondChecked->pop(); // Wait until the second coroutine verified its own value.
            $values['first'] = Integration::getTransaction();
            $finished->push(true);
        });

        Coroutine::create(function () use ($firstSet, $secondChecked, $finished, &$values): void {
            $firstSet->pop(); // Wait until the first coroutine set 'A'.
            Integration::setTransaction('B');
            $values['second'] = Integration::getTransaction();
            $secondChecked->push(true); // Resume the first coroutine.
            $finished->push(true);
        });

        $finished->pop();
        $finished->pop();

        $this->assertSame('A', $values['first']);
        $this->assertSame('B', $values['second']);
    }

    public function testRunningInCommandDefaultsToFalseAndCanBeEnabledWithinCoroutine(): void
    {
        $this->assertFalse(Constants::runningInCommand());

        Constants::setRunningInCommand();
        $this->assertTrue(Constants::runningInCommand());

        Constants::setRunningInCommand(false);
        $this->assertFalse(Constants::runningInCommand());
    }

    public function testRunningInCommandIsIsolatedBetweenCoroutines(): void
    {
        $values = [];
        $firstSet = new Channel(1);
        $secondChecked = new Channel(1);
        $finished = new Channel(2);

        Coroutine::create(function () use ($firstSet, $secondChecked, $finished, &$values): void {
            Constants::setRunningInCommand();
            $firstSet->push(true); // Signal that the flag has been set.
            $secondChecked->pop(); // Wait until the second coroutine verified its own value.
            $values['first'] = Constants::runningInCommand();
            $finished->push(true);
        });

        Coroutine::create(function () use ($firstSet, $secondChecked, $finished, &$values): void {
            $firstSet->pop(); // Wait until the first coroutine set the flag.
            $values['second'] = Constants::runningInCommand(); // Unset in this coroutine.
            $secondChecked->push(true); // Resume the first coroutine.
            $finished->push(true);
        });

        $finished->pop();
        $finished->pop();

        $this->assertTrue($values['first']);
        $this->assertFalse($values['second']);
    }
}
