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
use FriendsOfHyperf\Tinker\TinkerCaster;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Stringable;

uses()->group('tinker');

test('test CanCastCollection', function () {
    $caster = new TinkerCaster();

    $result = $caster->castCollection(new Collection(['foo', 'bar']));

    $this->assertSame([['foo', 'bar']], array_values($result));
});

test('test CanCastHtmlString', function () {
    $caster = new TinkerCaster();

    $result = $caster->castHtmlString(new HtmlString('<p>foo</p>'));

    $this->assertSame(['<p>foo</p>'], array_values($result));
});

test('test CanCastStringable', function () {
    $caster = new TinkerCaster();

    $result = $caster->castStringable(new Stringable('test string'));

    $this->assertSame(['test string'], array_values($result));
});
