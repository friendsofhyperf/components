<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Foundation\HtmlString;

uses()->group('macros', 'html-string');

test('test toHtml', function () {
    $str = '<h1>foo</h1>';
    $html = new HtmlString('<h1>foo</h1>');
    expect($html->toHtml())->toBe($str);
});

test('test toString', function () {
    $str = '<h1>foo</h1>';
    $html = new HtmlString('<h1>foo</h1>');
    expect((string) $html)->toBe($str);
});

test('test isEmpty', function () {
    expect((new HtmlString(''))->isEmpty())->toBeTrue();
});

test('test isNotEmpty', function () {
    expect((new HtmlString('foo'))->isNotEmpty())->toBeTrue();
});
