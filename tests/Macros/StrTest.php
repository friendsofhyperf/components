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

use FriendsOfHyperf\Tests\TestCase;
use Hyperf\Utils\Str;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class StrTest extends TestCase
{
    public function testStrBetweenFirst()
    {
        $this->assertSame('abc', Str::betweenFirst('abc', '', 'c'));
        $this->assertSame('abc', Str::betweenFirst('abc', 'a', ''));
        $this->assertSame('abc', Str::betweenFirst('abc', '', ''));
        $this->assertSame('b', Str::betweenFirst('abc', 'a', 'c'));
        $this->assertSame('b', Str::betweenFirst('dddabc', 'a', 'c'));
        $this->assertSame('b', Str::betweenFirst('abcddd', 'a', 'c'));
        $this->assertSame('b', Str::betweenFirst('dddabcddd', 'a', 'c'));
        $this->assertSame('nn', Str::betweenFirst('hannah', 'ha', 'ah'));
        $this->assertSame('a', Str::betweenFirst('[a]ab[b]', '[', ']'));
        $this->assertSame('foo', Str::betweenFirst('foofoobar', 'foo', 'bar'));
        $this->assertSame('', Str::betweenFirst('foobarbar', 'foo', 'bar'));
    }

    public function testFlushCache()
    {
        $reflection = new ReflectionClass(Str::class);
        $property = $reflection->getProperty('snakeCache');
        $property->setAccessible(true);

        Str::flushCache();
        $this->assertEmpty($property->getValue());

        Str::snake('Taylor Otwell');
        $this->assertNotEmpty($property->getValue());

        Str::flushCache();
        $this->assertEmpty($property->getValue());
    }

    public function testExcerpt()
    {
        $this->assertSame('...is a beautiful morn...', Str::excerpt('This is a beautiful morning', 'beautiful', ['radius' => 5]));
        $this->assertSame('This is a...', Str::excerpt('This is a beautiful morning', 'this', ['radius' => 5]));
        $this->assertSame('...iful morning', Str::excerpt('This is a beautiful morning', 'morning', ['radius' => 5]));
        $this->assertNull(Str::excerpt('This is a beautiful morning', 'day'));
        $this->assertSame('...is a beautiful! mor...', Str::excerpt('This is a beautiful! morning', 'Beautiful', ['radius' => 5]));
        $this->assertSame('...is a beautiful? mor...', Str::excerpt('This is a beautiful? morning', 'beautiful', ['radius' => 5]));
        $this->assertSame('', Str::excerpt('', '', ['radius' => 0]));
        $this->assertSame('a', Str::excerpt('a', 'a', ['radius' => 0]));
        // $this->assertSame('...b...', Str::excerpt('abc', 'B', ['radius' => 0]));
        $this->assertSame('abc', Str::excerpt('abc', 'b', ['radius' => 1]));
        $this->assertSame('abc...', Str::excerpt('abcd', 'b', ['radius' => 1]));
        $this->assertSame('...abc', Str::excerpt('zabc', 'b', ['radius' => 1]));
        $this->assertSame('...abc...', Str::excerpt('zabcd', 'b', ['radius' => 1]));
        $this->assertSame('zabcd', Str::excerpt('zabcd', 'b', ['radius' => 2]));
        $this->assertSame('zabcd', Str::excerpt('  zabcd  ', 'b', ['radius' => 4]));
        $this->assertSame('...abc...', Str::excerpt('z  abc  d', 'b', ['radius' => 1]));
        $this->assertSame('[...]is a beautiful morn[...]', Str::excerpt('This is a beautiful morning', 'beautiful', ['omission' => '[...]', 'radius' => 5]));
        $this->assertSame(
            'This is the ultimate supercalifragilisticexpialidoceous very looooooooooooooooooong looooooooooooong beautiful morning with amazing sunshine and awesome tempera[...]',
            Str::excerpt(
                'This is the ultimate supercalifragilisticexpialidoceous very looooooooooooooooooong looooooooooooong beautiful morning with amazing sunshine and awesome temperatures. So what are you gonna do about it?',
                'very',
                ['omission' => '[...]'],
            )
        );

        $this->assertSame('...y...', Str::excerpt('taylor', 'y', ['radius' => 0]));
        $this->assertSame('...ayl...', Str::excerpt('taylor', 'Y', ['radius' => 1]));
        $this->assertSame('<div> The article description </div>', Str::excerpt('<div> The article description </div>', 'article'));
        $this->assertSame('...The article desc...', Str::excerpt('<div> The article description </div>', 'article', ['radius' => 5]));
        $this->assertSame('The article description', Str::excerpt(strip_tags('<div> The article description </div>'), 'article'));
        $this->assertSame('', Str::excerpt(null));
        $this->assertSame('', Str::excerpt(''));
        $this->assertSame('', Str::excerpt(null, ''));
        $this->assertSame('T...', Str::excerpt('The article description', null, ['radius' => 1]));
        $this->assertSame('The arti...', Str::excerpt('The article description', '', ['radius' => 8]));
        $this->assertSame('', Str::excerpt(' '));
        $this->assertSame('The arti...', Str::excerpt('The article description', ' ', ['radius' => 4]));
        $this->assertSame('...cle description', Str::excerpt('The article description', 'description', ['radius' => 4]));
        $this->assertSame('T...', Str::excerpt('The article description', 'T', ['radius' => 0]));
        $this->assertSame('What i?', Str::excerpt('What is the article?', 'What', ['radius' => 2, 'omission' => '?']));

        $this->assertSame('...ö - 二 sān 大åè...', Str::excerpt('åèö - 二 sān 大åèö', '二 sān', ['radius' => 4]));
        $this->assertSame('åèö - 二...', Str::excerpt('åèö - 二 sān 大åèö', 'åèö', ['radius' => 4]));
        $this->assertSame('åèö - 二 sān 大åèö', Str::excerpt('åèö - 二 sān 大åèö', 'åèö - 二 sān 大åèö', ['radius' => 4]));
        $this->assertSame('åèö - 二 sān 大åèö', Str::excerpt('åèö - 二 sān 大åèö', 'åèö - 二 sān 大åèö', ['radius' => 4]));
        $this->assertSame('...༼...', Str::excerpt('㏗༼㏗', '༼', ['radius' => 0]));
        $this->assertSame('...༼...', Str::excerpt('㏗༼㏗', '༼', ['radius' => 0]));
        $this->assertSame('...ocê e...', Str::excerpt('Como você está', 'ê', ['radius' => 2]));
        $this->assertSame('...ocê e...', Str::excerpt('Como você está', 'Ê', ['radius' => 2]));
        $this->assertSame('João...', Str::excerpt('João Antônio ', 'jo', ['radius' => 2]));
        $this->assertSame('João Antô...', Str::excerpt('João Antônio', 'JOÃO', ['radius' => 5]));
    }

    public function testHeadline()
    {
        $this->assertSame('Jefferson Costella', Str::headline('jefferson costella'));
        $this->assertSame('Jefferson Costella', Str::headline('jefFErson coSTella'));
        $this->assertSame('Jefferson Costella Uses Laravel', Str::headline('jefferson_costella uses-_Laravel'));
        $this->assertSame('Jefferson Costella Uses Laravel', Str::headline('jefferson_costella uses__Laravel'));

        $this->assertSame('Laravel P H P Framework', Str::headline('laravel_p_h_p_framework'));
        $this->assertSame('Laravel P H P Framework', Str::headline('laravel _p _h _p _framework'));
        $this->assertSame('Laravel Php Framework', Str::headline('laravel_php_framework'));
        $this->assertSame('Laravel Ph P Framework', Str::headline('laravel-phP-framework'));
        $this->assertSame('Laravel Php Framework', Str::headline('laravel  -_-  php   -_-   framework   '));

        $this->assertSame('Foo Bar', Str::headline('fooBar'));
        $this->assertSame('Foo Bar', Str::headline('foo_bar'));
        $this->assertSame('Foo Bar Baz', Str::headline('foo-barBaz'));
        $this->assertSame('Foo Bar Baz', Str::headline('foo-bar_baz'));

        $this->assertSame('Öffentliche Überraschungen', Str::headline('öffentliche-überraschungen'));
        $this->assertSame('Öffentliche Überraschungen', Str::headline('-_öffentliche_überraschungen_-'));
        $this->assertSame('Öffentliche Überraschungen', Str::headline('-öffentliche überraschungen'));

        $this->assertSame('Sind Öde Und So', Str::headline('sindÖdeUndSo'));

        $this->assertSame('Orwell 1984', Str::headline('orwell 1984'));
        $this->assertSame('Orwell 1984', Str::headline('orwell   1984'));
        $this->assertSame('Orwell 1984', Str::headline('-orwell-1984 -'));
        $this->assertSame('Orwell 1984', Str::headline(' orwell_- 1984 '));
    }

    public function testIsJson()
    {
        $this->assertTrue(Str::isJson('1'));
        $this->assertTrue(Str::isJson('[1,2,3]'));
        $this->assertTrue(Str::isJson('[1,   2,   3]'));
        $this->assertTrue(Str::isJson('{"first": "John", "last": "Doe"}'));
        $this->assertTrue(Str::isJson('[{"first": "John", "last": "Doe"}, {"first": "Jane", "last": "Doe"}]'));

        $this->assertFalse(Str::isJson('1,'));
        $this->assertFalse(Str::isJson('[1,2,3'));
        $this->assertFalse(Str::isJson('[1,   2   3]'));
        $this->assertFalse(Str::isJson('{first: "John"}'));
        $this->assertFalse(Str::isJson('[{first: "John"}, {first: "Jane"}]'));
        $this->assertFalse(Str::isJson(''));
        $this->assertFalse(Str::isJson(null));
        $this->assertFalse(Str::isJson([]));
    }

    public function testLcfirst()
    {
        $this->assertSame('laravel', Str::lcfirst('Laravel'));
        $this->assertSame('laravel framework', Str::lcfirst('Laravel framework'));
        $this->assertSame('мама', Str::lcfirst('Мама'));
        $this->assertSame('мама мыла раму', Str::lcfirst('Мама мыла раму'));
    }

    public function testUuid()
    {
        $this->assertInstanceOf(UuidInterface::class, Str::uuid());
        $this->assertInstanceOf(UuidInterface::class, Str::orderedUuid());
    }

    public function testUcsplit()
    {
        $this->assertSame(['Laravel_p_h_p_framework'], Str::ucsplit('Laravel_p_h_p_framework'));
        $this->assertSame(['Laravel_', 'P_h_p_framework'], Str::ucsplit('Laravel_P_h_p_framework'));
        $this->assertSame(['laravel', 'P', 'H', 'P', 'Framework'], Str::ucsplit('laravelPHPFramework'));
        $this->assertSame(['Laravel-ph', 'P-framework'], Str::ucsplit('Laravel-phP-framework'));

        $this->assertSame(['Żółta', 'Łódka'], Str::ucsplit('ŻółtaŁódka'));
        $this->assertSame(['sind', 'Öde', 'Und', 'So'], Str::ucsplit('sindÖdeUndSo'));
        $this->assertSame(['Öffentliche', 'Überraschungen'], Str::ucsplit('ÖffentlicheÜberraschungen'));
    }

    public function testIsUuidWithValidUuid()
    {
        $this->assertTrue(Str::isUuid(Str::uuid()->__toString()));
    }

    public function testIsUuidWithInvalidUuid()
    {
        $this->assertFalse(Str::isUuid('foo'));
    }

    public function testWordCount()
    {
        $this->assertEquals(2, Str::wordCount('Hello, world!'));
        $this->assertEquals(10, Str::wordCount('Hi, this is my first contribution to the Laravel framework.'));
    }

    public function testMarkdown()
    {
        $this->assertSame("<p><em>hello world</em></p>\n", Str::markdown('*hello world*'));
        $this->assertSame("<h1>hello world</h1>\n", Str::markdown('# hello world'));
    }

    public function testInlineMarkdown()
    {
        $this->assertSame("<em>hello world</em>\n", Str::inlineMarkdown('*hello world*'));
        $this->assertSame("<a href=\"https://laravel.com\"><strong>Laravel</strong></a>\n", Str::inlineMarkdown('[**Laravel**](https://laravel.com)'));
    }

    public function testReverse()
    {
        $this->assertSame('FooBar', Str::reverse('raBooF'));
        $this->assertSame('Teniszütő', Str::reverse('őtüzsineT'));
        $this->assertSame('❤MultiByte☆', Str::reverse('☆etyBitluM❤'));
    }

    public function testSquish()
    {
        $this->assertSame('laravel php framework', Str::squish(' laravel   php  framework '));
        $this->assertSame('laravel php framework', Str::squish("laravel\t\tphp\n\nframework"));
        $this->assertSame('laravel php framework', Str::squish('
            laravel
            php
            framework
        '));
        $this->assertSame('laravel php framework', Str::squish('   laravel   php   framework   '));
        $this->assertSame('123', Str::squish('   123    '));
        $this->assertSame('だ', Str::squish('だ'));
        $this->assertSame('ム', Str::squish('ム'));
        $this->assertSame('だ', Str::squish('   だ    '));
        $this->assertSame('ム', Str::squish('   ム    '));
        $this->assertSame('ム', Str::squish('﻿   ム ﻿﻿   ﻿'));
        $this->assertSame('laravel php framework', Str::squish('laravelㅤㅤㅤphpㅤframework'));
    }

    public function testSubstrReplace()
    {
        $this->assertSame('12:00', Str::substrReplace('1200', ':', 2, 0));
        $this->assertSame('The Laravel Framework', Str::substrReplace('The Framework', 'Laravel ', 4, 0));
        $this->assertSame('Laravel – The PHP Framework for Web Artisans', Str::substrReplace('Laravel Framework', '– The PHP Framework for Web Artisans', 8));
    }

    public function testSwapKeywords(): void
    {
        $this->assertSame(
            'PHP 8 is fantastic',
            Str::swap([
                'PHP' => 'PHP 8',
                'awesome' => 'fantastic',
            ], 'PHP is awesome')
        );

        $this->assertSame(
            'foo bar baz',
            Str::swap([
                'ⓐⓑ' => 'baz',
            ], 'foo bar ⓐⓑ')
        );
    }

    /**
     * @dataProvider specialCharacterProvider
     */
    public function testTransliterate(string $value, string $expected): void
    {
        $this->assertSame($expected, Str::transliterate($value));
    }

    public function specialCharacterProvider(): array
    {
        return [
            ['ⓐⓑⓒⓓⓔⓕⓖⓗⓘⓙⓚⓛⓜⓝⓞⓟⓠⓡⓢⓣⓤⓥⓦⓧⓨⓩ', 'abcdefghijklmnopqrstuvwxyz'],
            ['⓪①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳', '01234567891011121314151617181920'],
            ['⓵⓶⓷⓸⓹⓺⓻⓼⓽⓾', '12345678910'],
            ['⓿⓫⓬⓭⓮⓯⓰⓱⓲⓳⓴', '011121314151617181920'],
            ['ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ', 'test@laravel.com'],
            ['🎂', '?'],
            ['abcdefghijklmnopqrstuvwxyz', 'abcdefghijklmnopqrstuvwxyz'],
            ['0123456789', '0123456789'],
        ];
    }

    public function testTransliterateOverrideUnknown(): void
    {
        $this->assertSame('HHH', Str::transliterate('🎂🚧🏆', 'H'));
        $this->assertSame('Hello', Str::transliterate('🎂', 'Hello'));
    }

    /**
     * @dataProvider specialCharacterProvider
     */
    public function testTransliterateStrict(string $value, string $expected): void
    {
        $this->assertSame($expected, Str::transliterate($value, '?', true));
    }
}
