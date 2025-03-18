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
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\Group('macros')]
class ContextTest extends TestCase
{
    public function testIncrementInNonCoroutineEnvironment()
    {
        $id = 'test.increment.non.coroutine';
        Context::destroy($id);

        // 测试初始递增
        $result = Context::increment($id);
        $this->assertSame(1, $result);

        // 测试再次递增
        $result = Context::increment($id);
        $this->assertSame(2, $result);

        // 测试指定步长递增
        $result = Context::increment($id, 3);
        $this->assertSame(5, $result);
    }

    public function testDecrementInNonCoroutineEnvironment()
    {
        $id = 'test.decrement.non.coroutine';
        Context::destroy($id);

        // 测试不存在的键递减
        $result = Context::decrement($id);
        $this->assertSame(-1, $result);

        // 测试已存在的键递减
        $result = Context::decrement($id);
        $this->assertSame(-2, $result);

        // 测试指定步长递减
        $result = Context::decrement($id, 3);
        $this->assertSame(-5, $result);
    }

    public function testIncrementInCoroutineEnvironment()
    {
        (new Waiter())->wait(function () {
            // 测试初始递增
            $id = 'test.co.increment';
            $result = Context::increment($id);
            $this->assertSame(1, $result);

            // 测试再次递增
            $result = Context::increment($id);
            $this->assertSame(2, $result);

            // 测试指定步长递增
            $result = Context::increment($id, 3);
            $this->assertSame(5, $result);
        });
    }

    public function testDecrementInCoroutineEnvironment()
    {
        (new Waiter())->wait(function () {
            // 测试初始递减
            $id = 'test.co.decrement';
            $result = Context::decrement($id);
            $this->assertSame(-1, $result);

            // 测试再次递减
            $result = Context::decrement($id);
            $this->assertSame(-2, $result);

            // 测试指定步长递减
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
            // 测试特定协程ID的递减
            $result = Context::decrement($id, 2, $cid);
            $this->assertSame(1, $result);
        });
    }
}
