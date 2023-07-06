<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Faker\Factory;
use Faker\Generator;
use Pest\Mock\Mock;

uses(\FriendsOfHyperf\Tests\TestCase::class)->in('*/*');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Creates a new mock with the given class or object.
 *
 * @template TObject as object
 *
 * @param class-string<TObject>|TObject $object
 * @return Mock<TObject>
 */
function mocking(string|object $object): Mock
{
    return new Mock($object);
}

/**
 * Returns the string "test_property".
 */
function test_property(): string
{
    return 'test_property';
}

function faker(string $locale = Factory::DEFAULT_LOCALE): Generator
{
    return Factory::create($locale);
}
