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

test('test deduplicate', function () {
    $this->assertSame(' laravel php framework ', Str::deduplicate(' laravel   php  framework '));
    $this->assertSame('what', Str::deduplicate('whaaat', 'a'));
    $this->assertSame('/some/odd/path/', Str::deduplicate('/some//odd//path/', '/'));
    $this->assertSame('ムだム', Str::deduplicate('ムだだム', 'だ'));
});

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
