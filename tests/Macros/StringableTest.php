<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Support\HtmlString;

uses()->group('macros', 'stringable');

test('test apa', function ($expected, $value) {
    expect((string) $this->stringable($value)->apa())->toBe($expected);
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

test('test toHtmlString', function () {
    $this->assertEquals(
        new HtmlString('<h1>Test string</h1>'),
        $this->stringable('<h1>Test string</h1>')->toHtmlString()
    );
});

test('test markdown', function ($expected, $markdown) {
    expect((string) $this->stringable($markdown)->markdown())->toBe($expected);
})->with([
    ["<p><em>hello world</em></p>\n", '*hello world*'],
    ["<h1>hello world</h1>\n", '# hello world'],
]);

test('test inlineMarkdown', function ($expected, $markdown) {
    expect((string) $this->stringable($markdown)->inlineMarkdown())->toBe($expected);
})->with([
    ["<em>hello world</em>\n", '*hello world*'],
    ["<a href=\"https://laravel.com\"><strong>Laravel</strong></a>\n", '[**Laravel**](https://laravel.com)'],
]);

test('test newLine', function () {
    $this->assertSame('Laravel' . PHP_EOL, (string) $this->stringable('Laravel')->newLine());
    $this->assertSame('foo' . PHP_EOL . PHP_EOL . 'bar', (string) $this->stringable('foo')->newLine(2)->append('bar'));
});

test('test take', function ($expected, $args) {
    expect((string) $this->stringable(array_shift($args))->take(...$args))->toBe($expected);
})->with([
    ['ab', ['abcdef', 2]],
    ['ef', ['abcdef', -2]],
]);

test('test whenIsAscii', function () {
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
});
