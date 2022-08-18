<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tests\Macros;

use FriendsOfHyperf\Macros\Foundation\HtmlString;
use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Utils\Str;
use Hyperf\Utils\Stringable;

/**
 * @internal
 * @coversNothing
 */
class StringableTest extends TestCase
{
    public function testBetweenFirst()
    {
        $this->assertSame('abc', (string) $this->stringable('abc')->betweenFirst('', 'c'));
        $this->assertSame('abc', (string) $this->stringable('abc')->betweenFirst('a', ''));
        $this->assertSame('abc', (string) $this->stringable('abc')->betweenFirst('', ''));
        $this->assertSame('b', (string) $this->stringable('abc')->betweenFirst('a', 'c'));
        $this->assertSame('b', (string) $this->stringable('dddabc')->betweenFirst('a', 'c'));
        $this->assertSame('b', (string) $this->stringable('abcddd')->betweenFirst('a', 'c'));
        $this->assertSame('b', (string) $this->stringable('dddabcddd')->betweenFirst('a', 'c'));
        $this->assertSame('nn', (string) $this->stringable('hannah')->betweenFirst('ha', 'ah'));
        $this->assertSame('a', (string) $this->stringable('[a]ab[b]')->betweenFirst('[', ']'));
        $this->assertSame('foo', (string) $this->stringable('foofoobar')->betweenFirst('foo', 'bar'));
        $this->assertSame('', (string) $this->stringable('foobarbar')->betweenFirst('foo', 'bar'));
    }

    public function testClassNamespace()
    {
        $this->assertEquals(
            Str::classNamespace(static::class),
            $this->stringable(static::class)->classNamespace()
        );
    }

    public function testExcerpt()
    {
        $this->assertSame('...is a beautiful morn...', (string) $this->stringable('This is a beautiful morning')->excerpt('beautiful', ['radius' => 5]));
    }

    public function testToHtmlString()
    {
        $this->assertEquals(
            new HtmlString('<h1>Test String</h1>'),
            $this->stringable('<h1>Test String</h1>')->toHtmlString()
        );
    }

    public function testIsJson()
    {
        $this->assertTrue($this->stringable('1')->isJson());
        $this->assertTrue($this->stringable('[1,2,3]')->isJson());
        $this->assertTrue($this->stringable('[1,   2,   3]')->isJson());
        $this->assertTrue($this->stringable('{"first": "John", "last": "Doe"}')->isJson());
        $this->assertTrue($this->stringable('[{"first": "John", "last": "Doe"}, {"first": "Jane", "last": "Doe"}]')->isJson());

        $this->assertFalse($this->stringable('1,')->isJson());
        $this->assertFalse($this->stringable('[1,2,3')->isJson());
        $this->assertFalse($this->stringable('[1,   2   3]')->isJson());
        $this->assertFalse($this->stringable('{first: "John"}')->isJson());
        $this->assertFalse($this->stringable('[{first: "John"}, {first: "Jane"}]')->isJson());
        $this->assertFalse($this->stringable('')->isJson());
        $this->assertFalse($this->stringable(null)->isJson());
    }

    public function testIsUuid()
    {
        $this->assertTrue($this->stringable('2cdc7039-65a6-4ac7-8e5d-d554a98e7b15')->isUuid());
        $this->assertFalse($this->stringable('2cdc7039-65a6-4ac7-8e5d-d554a98')->isUuid());
    }

    public function testMarkdown()
    {
        $this->assertEquals("<p><em>hello world</em></p>\n", $this->stringable('*hello world*')->markdown());
        $this->assertEquals("<h1>hello world</h1>\n", $this->stringable('# hello world')->markdown());
    }

    public function testInlineMarkdown()
    {
        $this->assertEquals("<em>hello world</em>\n", $this->stringable('*hello world*')->inlineMarkdown());
        $this->assertEquals("<a href=\"https://laravel.com\"><strong>Laravel</strong></a>\n", $this->stringable('[**Laravel**](https://laravel.com)')->inlineMarkdown());
    }

    public function testNewLine()
    {
        $this->assertSame('Laravel' . PHP_EOL, (string) $this->stringable('Laravel')->newLine());
        $this->assertSame('foo' . PHP_EOL . PHP_EOL . 'bar', (string) $this->stringable('foo')->newLine(2)->append('bar'));
    }

    public function testReverse()
    {
        $this->assertSame('FooBar', (string) $this->stringable('raBooF')->reverse());
        $this->assertSame('Teniszütő', (string) $this->stringable('őtüzsineT')->reverse());
        $this->assertSame('❤MultiByte☆', (string) $this->stringable('☆etyBitluM❤')->reverse());
    }

    public function testSquish()
    {
        $this->assertSame('words with spaces', (string) $this->stringable(' words  with   spaces ')->squish());
        $this->assertSame('words with spaces', (string) $this->stringable("words\t\twith\n\nspaces")->squish());
        $this->assertSame('words with spaces', (string) $this->stringable('
            words
            with
            spaces
        ')->squish());
    }

    public function testScan()
    {
        $this->assertSame([123456], $this->stringable('SN/123456')->scan('SN/%d')->toArray());
        $this->assertSame(['Otwell', 'Taylor'], $this->stringable('Otwell, Taylor')->scan('%[^,],%s')->toArray());
        $this->assertSame(['filename', 'jpg'], $this->stringable('filename.jpg')->scan('%[^.].%s')->toArray());
    }

    public function testSubstrReplace()
    {
        $this->assertSame('12:00', (string) $this->stringable('1200')->substrReplace(':', 2, 0));
        $this->assertSame('The Laravel Framework', (string) $this->stringable('The Framework')->substrReplace('Laravel ', 4, 0));
        $this->assertSame('Laravel – The PHP Framework for Web Artisans', (string) $this->stringable('Laravel Framework')->substrReplace('– The PHP Framework for Web Artisans', 8));
    }

    public function testSwap()
    {
        $this->assertSame('PHP 8 is fantastic', (string) $this->stringable('PHP is awesome')->swap([
            'PHP' => 'PHP 8',
            'awesome' => 'fantastic',
        ]));
    }

    public function testTest()
    {
        $stringable = $this->stringable('foo bar');

        $this->assertTrue($stringable->test('/bar/'));
        $this->assertTrue($stringable->test('/foo (.*)/'));
    }

    public function testWrap()
    {
        $this->assertEquals('This is me!', (string) $this->stringable('is')->wrap('This ', ' me!'));
        $this->assertEquals('"value"', (string) $this->stringable('value')->wrap('"'));
    }

    public function testWhenContains()
    {
        $this->assertSame('Tony Stark', (string) $this->stringable('stark')->whenContains('tar', function ($stringable) {
            return $stringable->prepend('Tony ')->title();
        }, function ($stringable) {
            return $stringable->prepend('Arno ')->title();
        }));

        $this->assertSame('stark', (string) $this->stringable('stark')->whenContains('xxx', function ($stringable) {
            return $stringable->prepend('Tony ')->title();
        }));

        $this->assertSame('Arno Stark', (string) $this->stringable('stark')->whenContains('xxx', function ($stringable) {
            return $stringable->prepend('Tony ')->title();
        }, function ($stringable) {
            return $stringable->prepend('Arno ')->title();
        }));
    }

    public function testWhenContainsAll()
    {
        $this->assertSame('Tony Stark', (string) $this->stringable('tony stark')->whenContainsAll(['tony', 'stark'], function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));

        $this->assertSame('tony stark', (string) $this->stringable('tony stark')->whenContainsAll(['xxx'], function ($stringable) {
            return $stringable->title();
        }));

        $this->assertSame('TonyStark', (string) $this->stringable('tony stark')->whenContainsAll(['tony', 'xxx'], function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));
    }

    public function testWhenEndsWith()
    {
        $this->assertSame('Tony Stark', (string) $this->stringable('tony stark')->whenEndsWith('ark', function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));

        $this->assertSame('Tony Stark', (string) $this->stringable('tony stark')->whenEndsWith(['kra', 'ark'], function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));

        $this->assertSame('tony stark', (string) $this->stringable('tony stark')->whenEndsWith(['xxx'], function ($stringable) {
            return $stringable->title();
        }));

        $this->assertSame('TonyStark', (string) $this->stringable('tony stark')->whenEndsWith(['tony', 'xxx'], function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));
    }

    public function testWhenExactly()
    {
        $this->assertSame('Nailed it...!', (string) $this->stringable('Tony Stark')->whenExactly('Tony Stark', function ($stringable) {
            return 'Nailed it...!';
        }, function ($stringable) {
            return 'Swing and a miss...!';
        }));

        $this->assertSame('Swing and a miss...!', (string) $this->stringable('Tony Stark')->whenExactly('Iron Man', function ($stringable) {
            return 'Nailed it...!';
        }, function ($stringable) {
            return 'Swing and a miss...!';
        }));

        $this->assertSame('Tony Stark', (string) $this->stringable('Tony Stark')->whenExactly('Iron Man', function ($stringable) {
            return 'Nailed it...!';
        }));
    }

    public function testWhenNotExactly()
    {
        $this->assertSame(
            'Iron Man',
            (string) $this->stringable('Tony')->whenNotExactly('Tony Stark', function ($stringable) {
                return 'Iron Man';
            })
        );

        $this->assertSame(
            'Swing and a miss...!',
            (string) $this->stringable('Tony Stark')->whenNotExactly('Tony Stark', function ($stringable) {
                return 'Iron Man';
            }, function ($stringable) {
                return 'Swing and a miss...!';
            })
        );
    }

    public function testWhenIs()
    {
        $this->assertSame('Winner: /', (string) $this->stringable('/')->whenIs('/', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }, function ($stringable) {
            return 'Try again';
        }));

        $this->assertSame('/', (string) $this->stringable('/')->whenIs(' /', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }));

        $this->assertSame('Try again', (string) $this->stringable('/')->whenIs(' /', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }, function ($stringable) {
            return 'Try again';
        }));

        $this->assertSame('Winner: foo/bar/baz', (string) $this->stringable('foo/bar/baz')->whenIs('foo/*', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }));
    }

    public function testWhenIsAscii()
    {
        $this->assertSame('Ascii: A', (string) $this->stringable('A')->whenIsAscii(function ($stringable) {
            return $stringable->prepend('Ascii: ');
        }, function ($stringable) {
            return $stringable->prepend('Not Ascii: ');
        }));

        $this->assertSame('ù', (string) $this->stringable('ù')->whenIsAscii(function ($stringable) {
            return $stringable->prepend('Ascii: ');
        }));

        $this->assertSame('Not Ascii: ù', (string) $this->stringable('ù')->whenIsAscii(function ($stringable) {
            return $stringable->prepend('Ascii: ');
        }, function ($stringable) {
            return $stringable->prepend('Not Ascii: ');
        }));
    }

    public function testWhenIsUuid()
    {
        $this->assertSame('Uuid: 2cdc7039-65a6-4ac7-8e5d-d554a98e7b15', (string) $this->stringable('2cdc7039-65a6-4ac7-8e5d-d554a98e7b15')->whenIsUuid(function ($stringable) {
            return $stringable->prepend('Uuid: ');
        }, function ($stringable) {
            return $stringable->prepend('Not Uuid: ');
        }));

        $this->assertSame('2cdc7039-65a6-4ac7-8e5d-d554a98', (string) $this->stringable('2cdc7039-65a6-4ac7-8e5d-d554a98')->whenIsUuid(function ($stringable) {
            return $stringable->prepend('Uuid: ');
        }));

        $this->assertSame('Not Uuid: 2cdc7039-65a6-4ac7-8e5d-d554a98', (string) $this->stringable('2cdc7039-65a6-4ac7-8e5d-d554a98')->whenIsUuid(function ($stringable) {
            return $stringable->prepend('Uuid: ');
        }, function ($stringable) {
            return $stringable->prepend('Not Uuid: ');
        }));
    }

    public function testWhenTest()
    {
        $this->assertSame('Winner: foo bar', (string) $this->stringable('foo bar')->whenTest('/bar/', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }, function ($stringable) {
            return 'Try again';
        }));

        $this->assertSame('Try again', (string) $this->stringable('foo bar')->whenTest('/link/', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }, function ($stringable) {
            return 'Try again';
        }));

        $this->assertSame('foo bar', (string) $this->stringable('foo bar')->whenTest('/link/', function ($stringable) {
            return $stringable->prepend('Winner: ');
        }));
    }

    public function testWhenStartsWith()
    {
        $this->assertSame('Tony Stark', (string) $this->stringable('tony stark')->whenStartsWith('ton', function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));

        $this->assertSame('Tony Stark', (string) $this->stringable('tony stark')->whenStartsWith(['ton', 'not'], function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));

        $this->assertSame('tony stark', (string) $this->stringable('tony stark')->whenStartsWith(['xxx'], function ($stringable) {
            return $stringable->title();
        }));

        $this->assertSame('Tony Stark', (string) $this->stringable('tony stark')->whenStartsWith(['tony', 'xxx'], function ($stringable) {
            return $stringable->title();
        }, function ($stringable) {
            return $stringable->studly();
        }));
    }

    public function testValueAndToString()
    {
        $this->assertSame('foo', $this->stringable('foo')->value());
        $this->assertSame('foo', $this->stringable('foo')->toString());
    }

    protected function stringable($value = '')
    {
        return new Stringable($value);
    }
}
