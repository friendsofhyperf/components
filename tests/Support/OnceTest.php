<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Support\Once\Cache;

use function FriendsOfHyperf\Support\once;

beforeEach(function () {
    $this->cache = Cache::getInstance();
    $this->cache->enable();
    $this->cache->flush();
});

it('will run the a callback without arguments only once', function () {
    $testClass = new class() {
        public function getNumber()
        {
            return once(function () {
                return rand(1, 10000000);
            });
        }
    };

    $firstResult = $testClass->getNumber();

    expect($firstResult)->toBeGreaterThanOrEqual(1);
    expect($firstResult)->toBeLessThanOrEqual(10000000);

    foreach (range(1, 100) as $i) {
        expect($testClass->getNumber())->toBe($firstResult);
    }
});

it('will run the given callback only once per variation arguments in use', function () {
    $testClass = new class() {
        public function getNumberForLetter($letter)
        {
            return once(function () use ($letter) {
                return $letter . rand(1, 10000000);
            });
        }
    };

    foreach (range('A', 'Z') as $letter) {
        $firstResult = $testClass->getNumberForLetter($letter);
        expect($firstResult)->toStartWith($letter);

        foreach (range(1, 100) as $i) {
            expect($testClass->getNumberForLetter($letter))->toBe($firstResult);
        }
    }
});

it('will run the given callback only once for falsy result', function () {
    $testClass = new class() {
        public $counter = 0;

        public function getNull()
        {
            return once(function () {
                ++$this->counter;
            });
        }
    };

    expect($testClass->getNull())->toBeNull();
    expect($testClass->getNull())->toBeNull();
    expect($testClass->getNull())->toBeNull();

    expect($testClass->counter)->toBe(1);
});

it('will work properly with unset objects', function () {
    $previousNumbers = [];

    foreach (range(1, 5) as $number) {
        $testClass = new TestClass();

        $number = $testClass->getRandomNumber();

        expect($previousNumbers)->not()->toContain($number);

        $previousNumbers[] = $number;

        unset($testClass);
    }
});

it('will remember the memoized value when serialized when called in the same request', function () {
    $testClass = new TestClass();

    $firstNumber = $testClass->getRandomNumber();

    expect($testClass->getRandomNumber())->toBe($firstNumber);

    $serialized = serialize($testClass);
    $unserialized = unserialize($serialized);
    unset($unserialized);

    expect($testClass->getRandomNumber())->toBe($firstNumber);
});

it('will run callback once on static method', function () {
    $object = new class() {
        public static function getNumber()
        {
            return once(function () {
                return rand(1, 10000000);
            });
        }
    };
    $class = get_class($object);

    $firstResult = $class::getNumber();

    expect($firstResult)->toBeGreaterThanOrEqual(1);
    expect($firstResult)->toBeLessThanOrEqual(10000000);

    foreach (range(1, 100) as $i) {
        expect($class::getNumber())->toBe($firstResult);
    }
});

it('will run callback once on static method per variation arguments in use', function () {
    $object = new class() {
        public static function getNumberForLetter($letter)
        {
            return once(function () use ($letter) {
                return $letter . rand(1, 10000000);
            });
        }
    };
    $class = get_class($object);

    foreach (range('A', 'Z') as $letter) {
        $firstResult = $class::getNumberForLetter($letter);
        expect($firstResult)->toStartWith($letter);

        foreach (range(1, 100) as $i) {
            expect($class::getNumberForLetter($letter))->toBe($firstResult);
        }
    }
});

it('can flush the entire cache', function () {
    $testClass = new class() {
        public function getNumber()
        {
            return once(function () {
                return random_int(1, 10000000);
            });
        }
    };

    $firstResult = $testClass->getNumber();

    Cache::getInstance()->flush();

    expect($testClass->getNumber())->not()->toBe($firstResult);
});

it('can enable and disable the cache', function () {
    $testClass = new class() {
        public function getNumber()
        {
            return once(function () {
                return random_int(1, 10000000);
            });
        }
    };

    expect($this->cache->isEnabled())->toBeTrue();
    expect($testClass->getNumber())->toBe($testClass->getNumber());

    $this->cache->disable();
    expect($this->cache->isEnabled())->toBeFalse();
    expect($testClass->getNumber())->not()->toBe($testClass->getNumber());

    $this->cache->enable();
    expect($this->cache->isEnabled())->toBeTrue();
    expect($testClass->getNumber())->toBe($testClass->getNumber());
});

it('will not throw error with eval', function () {
    $result = eval('return FriendsOfHyperf\Support\once( function () { return random_int(1, 1000); } ) ;');

    expect(in_array($result, range(1, 1000)))->toBeTrue();
});

it('will differentiate between closures', function () {
    $testClass = new class() {
        public function getNumber()
        {
            $closure = function () {
                return once(function () {
                    return random_int(1, 1000);
                });
            };

            return $closure();
        }

        public function secondNumber()
        {
            $closure = function () {
                return once(function () {
                    return random_int(1001, 2000);
                });
            };

            return $closure();
        }
    };

    expect($testClass->secondNumber())->not()->toBe($testClass->getNumber());
});

it('will run callback once for closure called on differemt lines', function () {
    $testClass = new class() {
        public function getNumbers()
        {
            $closure = function () {
                return once(function () {
                    return random_int(1, 10000000);
                });
            };

            $numbers[] = $closure();
            $numbers[] = $closure();

            return $numbers;
        }
    };

    $results = $testClass->getNumbers();
    expect($results[1])->toBe($results[0]);
});

it('will work in global functions', function () {
    function globalFunction()
    {
        return once(function () {
            return random_int(1, 10000000);
        });
    }

    expect(globalFunction())->toBe(globalFunction());
});

it('will work with two static functions with the same name', function () {
    $a = new class() {
        public static function getName()
        {
            return once(function () {
                return 'A';
            });
        }
    };

    $b = new class() {
        public static function getName()
        {
            return once(function () {
                return 'B';
            });
        }
    };

    $aClass = get_class($a);
    $bClass = get_class($b);

    expect($aClass::getName())->toBe('A');
    expect($bClass::getName())->toBe('B');
});

it('can count the items in the cache', function () {
    expect($this->cache->count())->toBe(0);

    $testClass = (new TestClass());
    $testClass->getRandomNumber();
    expect($this->cache->count())->toBe(1);

    $anotherTestClass = (new TestClass());
    $anotherTestClass->getRandomNumber();
    expect($this->cache->count())->toBe(2);
});

class TestClass
{
    protected int $randomNumber;

    public function __construct()
    {
        $this->randomNumber = rand(1, 1000000);
    }

    public function getRandomNumber()
    {
        return once(function () {
            return $this->randomNumber;
        });
    }
}
