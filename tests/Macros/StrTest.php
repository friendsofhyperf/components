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
