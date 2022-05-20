<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/helpers.
 *
 * @link     https://github.com/friendsofhyperf/helpers
 * @document https://github.com/friendsofhyperf/helpers/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\Tests;

use Hyperf\Utils\Stringable;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class HelpersTest extends TestCase
{
    public function testClassNamespace()
    {
        $this->assertSame('Foo\Bar', class_namespace('Foo\Bar\Baz'));
        $this->assertSame('', class_namespace('Baz'));
    }

    public function testObjectGet()
    {
        $class = new stdClass();
        $class->name = new stdClass();
        $class->name->first = 'Taylor';

        $this->assertSame('Taylor', object_get($class, 'name.first'));
    }

    public function providesPregReplaceArrayData()
    {
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
    }

    /**
     * @dataProvider providesPregReplaceArrayData
     * @param mixed $pattern
     * @param mixed $replacements
     * @param mixed $subject
     * @param mixed $expectedOutput
     */
    public function testPregReplaceArray($pattern, $replacements, $subject, $expectedOutput)
    {
        $this->assertSame(
            $expectedOutput,
            preg_replace_array($pattern, $replacements, $subject)
        );
    }

    public function testStr()
    {
        $stringable = str('string-value');

        $this->assertInstanceOf(Stringable::class, $stringable);
        $this->assertSame('string-value', (string) $stringable);

        $stringable = str($name = null);
        $this->assertInstanceOf(Stringable::class, $stringable);
        $this->assertTrue($stringable->isEmpty());

        $strAccessor = str();
        $this->assertTrue((new \ReflectionClass($strAccessor))->isAnonymous());
        $this->assertSame($strAccessor->limit('string-value', 3), 'str...');

        $strAccessor = str();
        $this->assertTrue((new \ReflectionClass($strAccessor))->isAnonymous());
        $this->assertSame((string) $strAccessor, '');
    }
}
