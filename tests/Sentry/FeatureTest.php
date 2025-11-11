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

use FriendsOfHyperf\Sentry\Feature;
use Hyperf\Config\Config;

beforeEach(function () {
    $config = new Config([
        'sentry' => [
            'tracing_tags' => [
                'foo.bar' => true,
                'foo.baz' => true,
                'foo.bar.baz' => false,
            ],
        ],
    ]);
    $this->feature = new Feature($config);
});

test('test is tracing tag enable', function ($key, $expected) {
    expect($this->feature->isTracingTagEnabled($key))->toBe($expected);
})->with([
    ['foo.bar', true],
    ['foo.baz', true],
    ['foo.bar.baz', false],
    ['foo.bay', false],
]);
