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

uses()->group('macros', 'str');

test('test apa', function ($expected, $value) {
    expect(Str::apa($value))->toBe($expected);
})->with([
    ['Tom and Jerry', 'tom and jerry'],
    ['Tom and Jerry', 'TOM AND JERRY'],
    ['Tom and Jerry', 'Tom And Jerry'],

    ['Back to the Future', 'back to the future'],
    ['Back to the Future', 'BACK TO THE FUTURE'],
    ['Back to the Future', 'Back To The Future'],

    ['This, Then That', 'this, then that'],
    ['This, Then That', 'THIS, THEN THAT'],
    ['This, Then That', 'This, Then That'],

    ['Bond. James Bond.', 'bond. james bond.'],
    ['Bond. James Bond.', 'BOND. JAMES BOND.'],
    ['Bond. James Bond.', 'Bond. James Bond.'],

    ['Self-Report', 'self-report'],
    ['Self-Report', 'Self-report'],
    ['Self-Report', 'SELF-REPORT'],

    ['As the World Turns, So Are the Days of Our Lives', 'as the world turns, so are the days of our lives'],
    ['As the World Turns, So Are the Days of Our Lives', 'AS THE WORLD TURNS, SO ARE THE DAYS OF OUR LIVES'],
    ['As the World Turns, So Are the Days of Our Lives', 'As The World Turns, So Are The Days Of Our Lives'],

    ['To Kill a Mockingbird', 'to kill a mockingbird'],
    ['To Kill a Mockingbird', 'TO KILL A MOCKINGBIRD'],
    ['To Kill a Mockingbird', 'To Kill A Mockingbird'],

    ['', ''],
    ['   ', '   '],
]);

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

test('test isUuidWithValidUuid', function () {
    $this->assertTrue(Str::isUuid(Str::uuid()->__toString()));
});

test('test isUuidWithInvalidUuid', function () {
    $this->assertFalse(Str::isUuid('foo'));
});

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

test('test position', function ($expected, $args) {
    expect(Str::position(...$args))->toBe($expected);
})->with([
    [7, ['Hello, World!', 'W']],
    [10, ['This is a test string.', 'test']],
    [23, ['This is a test string, test again.', 'test', 15]],
    [0, ['Hello, World!', 'Hello']],
    [7, ['Hello, World!', 'World!']],
    [10, ['This is a tEsT string.', 'tEsT', 0, 'UTF-8']],
    [7, ['Hello, World!', 'W', -6]],
    [18, ['Äpfel, Birnen und Kirschen', 'Kirschen', -10, 'UTF-8']],
    [9, ['@%€/=!"][$', '$', 0, 'UTF-8']],
    [false, ['Hello, World!', 'w', 0, 'UTF-8']],
    [false, ['Hello, World!', 'X', 0, 'UTF-8']],
    [false, ['', 'test']],
    [false, ['Hello, World!', 'X']],
]);

test('test take', function ($expected, $args) {
    expect(Str::take(...$args))->toBe($expected);
})->with([
    ['ab', ['abcdef', 2]],
    ['ef', ['abcdef', -2]],
]);

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
