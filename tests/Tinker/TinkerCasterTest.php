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
use FriendsOfHyperf\Tinker\TinkerCaster;
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Stringable;

beforeEach(function () {
    $this->caster = new TinkerCaster();
});

test('test cast collection', function () {
    $result = $this->caster->castCollection(new Collection(['foo', 'bar']));

    expect(array_values($result))->toBeArray()->toBe([['foo', 'bar']]);
});

test('test cast html string', function () {
    $result = $this->caster->castHtmlString(new HtmlString('<p>foo</p>'));

    expect(array_values($result))->toBeArray()->toBe(['<p>foo</p>']);
});

test('test cast stringable', function () {
    $result = $this->caster->castStringable(new Stringable('test string'));

    expect(array_values($result))->toBeArray()->toBe(['test string']);
});
