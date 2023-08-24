<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Stringable\Str;

test('test strBetweenFirst', function ($expected, $args) {
    expect(Str::betweenFirst(...$args))->toBe($expected);
})->with([
    ['abc', ['abc', '', 'c']],
    ['abc', ['abc', 'a', '']],
    ['abc', ['abc', '', '']],
    ['b', ['abc', 'a', 'c']],
    ['b', ['dddabc', 'a', 'c']],
    ['b', ['abcddd', 'a', 'c']],
    ['b', ['dddabcddd', 'a', 'c']],
    ['nn', ['hannah', 'ha', 'ah']],
    ['a', ['[a]ab[b]', '[', ']']],
    ['foo', ['foofoobar', 'foo', 'bar']],
    ['', ['foobarbar', 'foo', 'bar']],
]);

test('test excerpt', function () {
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
});

test('test headline', function ($expected, $value) {
    expect(Str::headline($value))->toBe($expected);
})->with([
    ['Jefferson Costella', 'jefferson costella'],
    ['Jefferson Costella', 'jefFErson coSTella'],
    ['Jefferson Costella Uses Laravel', 'jefferson_costella uses-_Laravel'],
    ['Jefferson Costella Uses Laravel', 'jefferson_costella uses__Laravel'],
    ['Laravel P H P Framework', 'laravel_p_h_p_framework'],
    ['Laravel P H P Framework', 'laravel _p _h _p _framework'],
    ['Laravel Php Framework', 'laravel_php_framework'],
    ['Laravel Ph P Framework', 'laravel-phP-framework'],
    ['Laravel Php Framework', 'laravel  -_-  php   -_-   framework   '],
    ['Foo Bar', 'fooBar'],
    ['Foo Bar', 'foo_bar'],
    ['Foo Bar Baz', 'foo-barBaz'],
    ['Foo Bar Baz', 'foo-bar_baz'],
    ['Öffentliche Überraschungen', 'öffentliche-überraschungen'],
    ['Öffentliche Überraschungen', '-_öffentliche_überraschungen_-'],
    ['Öffentliche Überraschungen', '-öffentliche überraschungen'],
    ['Sind Öde Und So', 'sindÖdeUndSo'],
    ['Orwell 1984', 'orwell 1984'],
    ['Orwell 1984', 'orwell   1984'],
    ['Orwell 1984', '-orwell-1984 -'],
    ['Orwell 1984', ' orwell_- 1984 '],
]);

test('test isJson', function ($expected, $value) {
    expect(Str::isJson($value))->toBe($expected);
})->with([
    [true, '1'],
    [true, '[1,2,3]'],
    [true, '[1,   2,   3]'],
    [true, '{"first": "John", "last": "Doe"}'],
    [true, '[{"first": "John", "last": "Doe"}, {"first": "Jane", "last": "Doe"}]'],
    [false, '1,'],
    [false, '[1,2,3'],
    [false, '[1,   2   3]'],
    [false, '{first: "John"}'],
    [false, '[{first: "John"}, {first: "Jane"}]'],
    [false, ''],
    [false, null],
    [false, []],
]);

test('test lcfirst', function ($expected, $value) {
    expect(Str::lcfirst($value))->toBe($expected);
})->with([
    ['laravel', 'Laravel'],
    ['laravel framework', 'Laravel framework'],
    ['мама', 'Мама'],
    ['мама мыла раму', 'Мама мыла раму'],
]);

test('test ucsplit', function ($expected, $value) {
    expect(Str::ucsplit($value))->toBe($expected);
})->with([
    [['Laravel_p_h_p_framework'], 'Laravel_p_h_p_framework'],
    [['Laravel_', 'P_h_p_framework'], 'Laravel_P_h_p_framework'],
    [['laravel', 'P', 'H', 'P', 'Framework'], 'laravelPHPFramework'],
    [['Laravel-ph', 'P-framework'], 'Laravel-phP-framework'],
    [['Żółta', 'Łódka'], 'ŻółtaŁódka'],
    [['sind', 'Öde', 'Und', 'So'], 'sindÖdeUndSo'],
    [['Öffentliche', 'Überraschungen'], 'ÖffentlicheÜberraschungen'],
]);

test('test isUuidWithValidUuid', function () {
    $this->assertTrue(Str::isUuid(Str::uuid()->__toString()));
});

test('test isUuidWithInvalidUuid', function () {
    $this->assertFalse(Str::isUuid('foo'));
});

test('test wordCount', function ($expected, $value) {
    expect(Str::wordCount($value))->toBe($expected);
})->with([
    [2, 'Hello, world!'],
    [10, 'Hi, this is my first contribution to the Laravel framework.'],
]);

test('test markdown', function ($expected, $value) {
    expect(Str::markdown($value))->toBe($expected);
})->with([
    ["<p><em>hello world</em></p>\n", '*hello world*'],
    ["<h1>hello world</h1>\n", '# hello world'],
]);

test('test inlineMarkdown', function ($expected, $value) {
    expect(Str::inlineMarkdown($value))->toBe($expected);
})->with([
    ["<em>hello world</em>\n", '*hello world*'],
    ["<a href=\"https://laravel.com\"><strong>Laravel</strong></a>\n", '[**Laravel**](https://laravel.com)'],
]);

test('test password', function ($expected, $args) {
    expect(Str::password(...$args))->toBeString()->toHaveLength($expected);
})->with([
    [32, []],
    [10, [10]],
]);

test('test replaceStart', function ($expected, $args) {
    expect(Str::replaceStart(...$args))->toBe($expected);
})->with([
    ['foobar foobar', ['bar', 'qux', 'foobar foobar']],
    ['foo/bar? foo/bar?', ['bar?', 'qux?', 'foo/bar? foo/bar?']],
    ['quxbar foobar', ['foo', 'qux', 'foobar foobar']],
    ['qux? foo/bar?', ['foo/bar?', 'qux?', 'foo/bar? foo/bar?']],
    ['bar foobar', ['foo', '', 'foobar foobar']],
    ['1', [0, '1', '0']],
    ['xxxnköping Malmö', ['Jö', 'xxx', 'Jönköping Malmö']],
    ['Jönköping Malmö', ['', 'yyy', 'Jönköping Malmö']],
]);

test('test replaceEnd', function ($expected, $args) {
    expect(Str::replaceEnd(...$args))->toBe($expected);
})->with([
    ['foobar fooqux', ['bar', 'qux', 'foobar foobar']],
    ['foo/bar? foo/qux?', ['bar?', 'qux?', 'foo/bar? foo/bar?']],
    ['foobar foo', ['bar', '', 'foobar foobar']],
    ['foobar foobar', ['xxx', 'yyy', 'foobar foobar']],
    ['foobar foobar', ['', 'yyy', 'foobar foobar']],
    ['fooxxx foobar', ['xxx', 'yyy', 'fooxxx foobar']],
    ['Malmö Jönköping', ['ö', 'xxx', 'Malmö Jönköping']],
    ['Malmö Jönkyyy', ['öping', 'yyy', 'Malmö Jönköping']],
]);

test('test reverse', function ($value, $expected) {
    expect(Str::reverse($value))->toBe($expected);
})->with([
    ['FooBar', 'raBooF'],
    ['Teniszütő', 'őtüzsineT'],
    ['❤MultiByte☆', '☆etyBitluM❤'],
]);

test('test squish', function ($expected, $value) {
    expect(Str::squish($value))->toBe($expected);
})->with([
    ['laravel php framework', ' laravel   php  framework '],
    ['laravel php framework', "laravel\t\tphp\n\nframework"],
    ['laravel php framework', '
        laravel
        php
        framework
    '],
    ['laravel php framework', 'laravelㅤㅤㅤphpㅤframework'],
    ['laravel php framework', 'laravelᅠᅠᅠᅠᅠᅠᅠᅠᅠᅠphpᅠᅠframework'],
    ['laravel php framework', '   laravel   php   framework   '],
    ['123', '   123    '],
    ['だ', 'だ'],
    ['ム', 'ム'],
    ['だ', '   だ    '],
    ['ム', '   ム    '],
    ['ム', '﻿   ム ﻿﻿   ﻿'],
]);

test('test substrReplace', function () {
    $this->assertSame('12:00', Str::substrReplace('1200', ':', 2, 0));
    $this->assertSame('The Laravel Framework', Str::substrReplace('The Framework', 'Laravel ', 4, 0));
    $this->assertSame('Laravel – The PHP Framework for Web Artisans', Str::substrReplace('Laravel Framework', '– The PHP Framework for Web Artisans', 8));
});

test('test swapKeywords', function (): void {
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
});

test('test transliterate', function (string $value, string $expected) {
    $this->assertSame($expected, Str::transliterate($value));
})->with('ialCharacterProvider');

dataset('ialCharacterProvider', [
    ['ⓐⓑⓒⓓⓔⓕⓖⓗⓘⓙⓚⓛⓜⓝⓞⓟⓠⓡⓢⓣⓤⓥⓦⓧⓨⓩ', 'abcdefghijklmnopqrstuvwxyz'],
    ['⓪①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳', '01234567891011121314151617181920'],
    ['⓵⓶⓷⓸⓹⓺⓻⓼⓽⓾', '12345678910'],
    ['⓿⓫⓬⓭⓮⓯⓰⓱⓲⓳⓴', '011121314151617181920'],
    ['ⓣⓔⓢⓣ@ⓛⓐⓡⓐⓥⓔⓛ.ⓒⓞⓜ', 'test@laravel.com'],
    ['🎂', '?'],
    ['abcdefghijklmnopqrstuvwxyz', 'abcdefghijklmnopqrstuvwxyz'],
    ['0123456789', '0123456789'],
]);

test('test transliterateOverrideUnknown', function ($args, $expected): void {
    expect(Str::transliterate(...$args))->toBe($expected);
})->with([
    [['🎂🚧🏆', 'H'], 'HHH'],
    [['🎂', 'Hello'], 'Hello'],
]);

test('test transliterateStrict', function (string $value, string $expected): void {
    $this->assertSame($expected, Str::transliterate($value, '?', true));
})->with('ialCharacterProvider');

test('test wrap', function () {
    $this->assertEquals('"value"', Str::wrap('value', '"'));
    $this->assertEquals('foo-bar-baz', Str::wrap('-bar-', 'foo', 'baz'));
});

test('test wordWrap', function ($expected, $args) {
    expect(Str::wordWrap(...$args))->toBe($expected);
})->with([
    ['Hello<br />World', ['Hello World', 3, '<br />']],
    ['Hel<br />lo<br />Wor<br />ld', ['Hello World', 3, '<br />', true]],
    ['❤Multi<br />Byte☆❤☆❤☆❤', ['❤Multi Byte☆❤☆❤☆❤', 3, '<br />']],
]);
