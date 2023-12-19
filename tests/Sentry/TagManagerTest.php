<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry;

use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Config\Config;

beforeEach(function () {
    $config = new Config([
        'sentry' => [
            'tracing' => [
                'tags' => [
                    'foo' => [
                        'bar' => 'foo.bar',
                        'baz' => 'foo.baz',
                        'bar.baz' => 'foo.bar.baz',
                    ],
                ],
            ],
        ],
    ]);
    $this->tagManager = new TagManager($config);
});

test('test has', function ($key, $expected) {
    expect($this->tagManager->has($key))->toBe($expected);
})->with([
    ['foo.bar', true],
    ['foo.baz', true],
    ['foo.bar.baz', true],
    ['foo.bay', false],
]);

test('test get', function ($key, $expected) {
    expect($this->tagManager->get($key))->toBe($expected);
})->with([
    ['foo.bar', 'foo.bar'],
    ['foo.baz', 'foo.baz'],
    ['foo.bar.baz', 'foo.bar.baz'],
    ['foo.bay', 'foo.bay'],
]);
