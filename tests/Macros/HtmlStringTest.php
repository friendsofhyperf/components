<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Foundation\HtmlString;

test('test ToHtml', function () {
    $str = '<h1>foo</h1>';
    $html = new HtmlString('<h1>foo</h1>');
    $this->assertEquals($str, $html->toHtml());
});

test('test ToString', function () {
    $str = '<h1>foo</h1>';
    $html = new HtmlString('<h1>foo</h1>');
    $this->assertEquals($str, (string) $html);
});

test('test IsEmpty', function () {
    $this->assertTrue((new HtmlString(''))->isEmpty());
});

test('test IsNotEmpty', function () {
    $this->assertTrue((new HtmlString('foo'))->isNotEmpty());
});
