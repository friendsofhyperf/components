<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Stringable\Stringable;

use function FriendsOfHyperf\Helpers\blank;
use function FriendsOfHyperf\Helpers\class_namespace;
use function FriendsOfHyperf\Helpers\Command\call;
use function FriendsOfHyperf\Helpers\filled;
use function FriendsOfHyperf\Helpers\object_get;
use function FriendsOfHyperf\Helpers\preg_replace_array;

test('test ClassNamespace', function () {
    $this->assertSame('Foo\Bar', class_namespace('Foo\Bar\Baz'));
    $this->assertSame('', class_namespace('Baz'));
});

test('test ObjectGet', function () {
    $class = new stdClass();
    $class->name = new stdClass();
    $class->name->first = 'Taylor';

    $this->assertSame('Taylor', object_get($class, 'name.first'));
});

dataset('providesPregReplaceArrayData', function () {
    $pointerArray = ['Taylor', 'Otwell'];

    next($pointerArray);

    return [
        ['/:[a-z_]+/', ['8:30', '9:00'], 'The event will take place between :start and :end', 'The event will take place between 8:30 and 9:00'],
        ['/%s/', ['Taylor'], 'Hi, %s', 'Hi, Taylor'],
        ['/%s/', ['Taylor', 'Otwell'], 'Hi, %s %s', 'Hi, Taylor Otwell'],
        ['/%s/', [], 'Hi, %s %s', 'Hi,  '],
        ['/%s/', ['a', 'b', 'c'], 'Hi', 'Hi'],
        ['//', [], '', ''],
        ['/%s/', ['a'], '', ''],
        // The internal pointer of this array is not at the beginning
        ['/%s/', $pointerArray, 'Hi, %s %s', 'Hi, Taylor Otwell'],
    ];
});

test('test PregReplaceArray', function ($pattern, $replacements, $subject, $expectedOutput) {
    $this->assertSame(
        $expectedOutput,
        preg_replace_array($pattern, $replacements, $subject)
    );
})->with('providesPregReplaceArrayData');

test('test FriendsOfHyperf\Helpers\Command\call', function () {
    $this->mock(ApplicationInterface::class, function ($mock) {
        $mock->shouldReceive('setAutoExit')->andReturnSelf();
        $mock->shouldReceive('run')->andReturn(0);
    });

    expect(call('foo:bar', ['argument' => 'value']))->toBe(0);
});

test('filled', function () {
    $this->assertFalse(filled(null));
    $this->assertFalse(filled(''));
    $this->assertFalse(filled('  '));
    $this->assertFalse(filled(new Stringable('')));
    $this->assertFalse(filled(new Stringable('  ')));
    $this->assertTrue(filled(10));
    $this->assertTrue(filled(true));
    $this->assertTrue(filled(false));
    $this->assertTrue(filled(0));
    $this->assertTrue(filled(0.0));
    $this->assertTrue(filled(new Stringable(' FooBar ')));
});

test('blank', function () {
    $this->assertTrue(blank(null));
    $this->assertTrue(blank(''));
    $this->assertTrue(blank('  '));
    $this->assertTrue(blank(new Stringable('')));
    $this->assertTrue(blank(new Stringable('  ')));
    $this->assertFalse(blank(10));
    $this->assertFalse(blank(true));
    $this->assertFalse(blank(false));
    $this->assertFalse(blank(0));
    $this->assertFalse(blank(0.0));
    $this->assertFalse(blank(new Stringable(' FooBar ')));
});
