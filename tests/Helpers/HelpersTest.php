<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Stringable;
use Mockery as m;
use Psr\Container\ContainerInterface;

uses(\FriendsOfHyperf\Tests\TestCase::class)->group('helpers');

afterEach(function () {
    m::close();
});

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

test('test Str', function () {
    $stringable = str('string-value');

    $this->assertInstanceOf(Stringable::class, $stringable);
    $this->assertSame('string-value', (string) $stringable);

    $stringable = str($name = null);
    $this->assertInstanceOf(Stringable::class, $stringable);
    $this->assertTrue($stringable->isEmpty());

    $strAccessor = str();
    $this->assertTrue((new ReflectionClass($strAccessor))->isAnonymous());
    $this->assertSame($strAccessor->limit('string-value', 3), 'str...');

    $strAccessor = str();
    $this->assertTrue((new ReflectionClass($strAccessor))->isAnonymous());
    $this->assertSame((string) $strAccessor, '');
});

test('test FriendsOfHyperf\Helpers\Command\call', function () {
    ApplicationContext::setContainer(
        mock(ContainerInterface::class)->expect(
            has: fn () => true,
            get: fn () => mock(ApplicationInterface::class)->expect(
                setAutoExit: fn () => null,
                run: fn () => 0,
            )
        )
    );

    expect(FriendsOfHyperf\Helpers\Command\call('command', ['argument' => 'value']))->toBe(0);
});
