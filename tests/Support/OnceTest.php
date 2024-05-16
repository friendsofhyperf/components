<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Support\Once;

use function FriendsOfHyperf\Support\once;

$letter = 'a';
$GLOBALS['onceable1'] = fn () => once(fn () => $letter . rand(1, PHP_INT_MAX));
$GLOBALS['onceable2'] = fn () => once(fn () => $letter . rand(1, PHP_INT_MAX));

function my_rand()
{
    return once(fn () => rand(1, PHP_INT_MAX));
}

class MyClass
{
    public function rand()
    {
        return once(fn () => rand(1, PHP_INT_MAX));
    }

    public static function staticRand()
    {
        return once(fn () => rand(1, PHP_INT_MAX));
    }

    public function callRand()
    {
        return once(fn () => $this->rand());
    }
}

afterEach(function () {
    Once::flush();
    Once::enable();
});

test('test ResultMemoization', function () {
    $instance = new class() {
        public function rand()
        {
            return once(fn () => rand(1, PHP_INT_MAX));
        }
    };

    $first = $instance->rand();
    $second = $instance->rand();

    $this->assertSame($first, $second);
});

test('test CallableIsCalledOnce', function () {
    $instance = new class() {
        public int $count = 0;

        public function increment()
        {
            return once(fn () => ++$this->count);
        }
    };

    $first = $instance->increment();
    $second = $instance->increment();

    $this->assertSame(1, $first);
    $this->assertSame(1, $second);
    $this->assertSame(1, $instance->count);
});

test('test Flush', function () {
    $instance = new MyClass();

    $first = $instance->rand();

    Once::flush();

    $second = $instance->rand();

    $this->assertNotSame($first, $second);

    Once::disable();
    Once::flush();

    $first = $instance->rand();
    $second = $instance->rand();

    $this->assertNotSame($first, $second);
});

test('test NotMemoizedWhenObjectIsGarbageCollected', function () {
    $instance = new MyClass();

    $first = $instance->rand();
    unset($instance);
    gc_collect_cycles();
    $instance = new MyClass();
    $second = $instance->rand();

    $this->assertNotSame($first, $second);
});

test('test IsNotMemoizedWhenCallableUsesChanges', function () {
    $instance = new class() {
        public function rand(string $letter)
        {
            return once(function () use ($letter) {
                return $letter . rand(1, 10000000);
            });
        }
    };

    $first = $instance->rand('a');
    $second = $instance->rand('b');

    $this->assertNotSame($first, $second);

    $first = $instance->rand('a');
    $second = $instance->rand('a');

    $this->assertSame($first, $second);

    $results = [];
    $letter = 'a';

    a:
    $results[] = once(fn () => $letter . rand(1, 10000000));

    if (count($results) < 2) {
        goto a;
    }

    $this->assertSame($results[0], $results[1]);
});

test('test UsageOfThis', function () {
    $instance = new MyClass();

    $first = $instance->callRand();
    $second = $instance->callRand();

    $this->assertSame($first, $second);
});

test('test Invokables', function () {
    $invokable = new class() {
        public static $count = 0;

        public function __invoke()
        {
            return self::$count = self::$count + 1;
        }
    };

    $instance = new class($invokable) {
        public function __construct(protected $invokable)
        {
        }

        public function call()
        {
            return once($this->invokable);
        }
    };

    $first = $instance->call();
    $second = $instance->call();
    $third = $instance->call();

    $this->assertSame($first, $second);
    $this->assertSame($first, $third);
    $this->assertSame(1, $invokable::$count);
});

test('test FirstClassCallableSyntax', function () {
    $instance = new class() {
        public function rand()
        {
            return once(MyClass::staticRand(...));
        }
    };

    $first = $instance->rand();
    $second = $instance->rand();

    $this->assertSame($first, $second);
});

test('test FirstClassCallableSyntaxWithArraySyntax', function () {
    $instance = new class() {
        public function rand()
        {
            return once([MyClass::class, 'staticRand']);
        }
    };

    $first = $instance->rand();
    $second = $instance->rand();

    $this->assertSame($first, $second);
});

test('test StaticMemoization', function () {
    $first = MyClass::staticRand();
    $second = MyClass::staticRand();

    $this->assertSame($first, $second);
});

test('test MemoizationWhenOnceIsWithinClosure', function () {
    $resolver = fn () => once(fn () => rand(1, PHP_INT_MAX));

    $first = $resolver();
    $second = $resolver();

    $this->assertSame($first, $second);
});

test('test MemoizationOnGlobalFunctions', function () {
    $first = my_rand();
    $second = my_rand();

    $this->assertSame($first, $second);
});

test('test Disable', function () {
    Once::disable();

    $first = my_rand();
    $second = my_rand();

    $this->assertNotSame($first, $second);
});

test('test TemporaryDisable', function () {
    $first = my_rand();
    $second = my_rand();

    Once::disable();

    $third = my_rand();

    Once::enable();

    $fourth = my_rand();

    $this->assertSame($first, $second);
    $this->assertNotSame($first, $third);
    $this->assertSame($first, $fourth);
});

test('test MemoizationWithinEvals', function () {
    $firstResolver = eval('return fn () => FriendsOfHyperf\Support\once( function () { return random_int(1, 1000); } ) ;');

    $firstA = $firstResolver();
    $firstB = $firstResolver();

    $secondResolver = eval('return fn () => fn () => FriendsOfHyperf\Support\once( function () { return random_int(1, 1000); } ) ;');

    $secondA = $secondResolver()();
    $secondB = $secondResolver()();

    $third = eval('return FriendsOfHyperf\Support\once( function () { return random_int(1, 1000); } ) ;');
    $fourth = eval('return FriendsOfHyperf\Support\once( function () { return random_int(1, 1000); } ) ;');

    $this->assertNotSame($firstA, $firstB);
    $this->assertNotSame($secondA, $secondB);
    $this->assertNotSame($third, $fourth);
});

test('test MemoizationOnSameLine', function () {
    $this->markTestSkipped('This test shows a limitation of the current implementation.');

    $result = [once(fn () => rand(1, PHP_INT_MAX)), once(fn () => rand(1, PHP_INT_MAX))];

    $this->assertNotSame($result[0], $result[1]);
});

test('test ResultIsDifferentWhenCalledFromDifferentClosures', function () {
    $resolver = fn () => once(fn () => rand(1, PHP_INT_MAX));
    $resolver2 = fn () => once(fn () => rand(1, PHP_INT_MAX));

    $first = $resolver();
    $second = $resolver2();

    $this->assertNotSame($first, $second);
});

test('test ResultIsMemoizedWhenCalledFromMethodsWithSameName', function () {
    $instanceA = new class() {
        public function rand()
        {
            return once(fn () => rand(1, PHP_INT_MAX));
        }
    };

    $instanceB = new class() {
        public function rand()
        {
            return once(fn () => rand(1, PHP_INT_MAX));
        }
    };

    $first = $instanceA->rand();
    $second = $instanceB->rand();

    $this->assertNotSame($first, $second);
});

test('test RecursiveOnceCalls', function () {
    $instance = new class() {
        public function rand()
        {
            return once(fn () => once(fn () => rand(1, PHP_INT_MAX)));
        }
    };

    $first = $instance->rand();
    $second = $instance->rand();

    $this->assertSame($first, $second);
});

test('test GlobalClosures', function () {
    $first = $GLOBALS['onceable1']();
    $second = $GLOBALS['onceable1']();

    $this->assertSame($first, $second);

    $third = $GLOBALS['onceable2']();
    $fourth = $GLOBALS['onceable2']();

    $this->assertSame($third, $fourth);

    $this->assertNotSame($first, $third);
});
