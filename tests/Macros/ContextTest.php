<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Macros;

use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Waiter;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('macros')]
class ContextTest extends TestCase
{
    public function testIncrementInNonCoroutineEnvironment()
    {
        $id = 'test.increment.non.coroutine';
        Context::destroy($id);

        // Test initial increment
        $result = Context::increment($id);
        $this->assertSame(1, $result);

        // Test increment again
        $result = Context::increment($id);
        $this->assertSame(2, $result);

        // Test increment with specific step
        $result = Context::increment($id, 3);
        $this->assertSame(5, $result);
    }

    public function testDecrementInNonCoroutineEnvironment()
    {
        $id = 'test.decrement.non.coroutine';
        Context::destroy($id);

        // Test decrement for non-existent key
        $result = Context::decrement($id);
        $this->assertSame(-1, $result);

        // Test decrement for existing key
        $result = Context::decrement($id);
        $this->assertSame(-2, $result);

        // Test decrement with specific step
        $result = Context::decrement($id, 3);
        $this->assertSame(-5, $result);
    }

    public function testIncrementInCoroutineEnvironment()
    {
        (new Waiter())->wait(function () {
            // Test initial increment
            $id = 'test.co.increment';
            $result = Context::increment($id);
            $this->assertSame(1, $result);

            // Test increment again
            $result = Context::increment($id);
            $this->assertSame(2, $result);

            // Test increment with specific step
            $result = Context::increment($id, 3);
            $this->assertSame(5, $result);
        });
    }

    public function testDecrementInCoroutineEnvironment()
    {
        (new Waiter())->wait(function () {
            // Test initial decrement
            $id = 'test.co.decrement';
            $result = Context::decrement($id);
            $this->assertSame(-1, $result);

            // Test decrement again
            $result = Context::decrement($id);
            $this->assertSame(-2, $result);

            // Test decrement with specific step
            $result = Context::decrement($id, 3);
            $this->assertSame(-5, $result);
        });
    }

    public function testIncrementWithSpecificCoroutineId()
    {
        $cid = Coroutine::id();
        $id = 'test.specific.increment';
        Context::set($id, 1);
        (new Waiter())->wait(function () use ($id, $cid) {
            $result = Context::increment($id, 1, $cid);
            $this->assertSame(2, $result);
        });
    }

    public function testDecrementWithSpecificCoroutineId()
    {
        $cid = Coroutine::id();
        $id = 'test.specific.decrement';
        Context::set($id, 3);
        (new Waiter())->wait(function () use ($id, $cid) {
            // Test decrement with specific coroutine ID
            $result = Context::decrement($id, 2, $cid);
            $this->assertSame(1, $result);
        });
    }
}
