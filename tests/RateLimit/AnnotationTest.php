<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\RateLimit\Annotation\RateLimit;

test('annotation can be instantiated with defaults', function () {
    $annotation = new RateLimit();

    expect($annotation->key)->toBe('');
    expect($annotation->maxAttempts)->toBe(60);
    expect($annotation->decay)->toBe(60);
    expect($annotation->algorithm)->toBe('fixed_window');
    expect($annotation->response)->toBe('Too Many Attempts.');
    expect($annotation->responseCode)->toBe(429);
});

test('annotation accepts custom parameters', function () {
    $annotation = new RateLimit(
        key: 'api:{ip}',
        maxAttempts: 100,
        decay: 120,
        algorithm: 'sliding_window',
        response: 'Custom message',
        responseCode: 503
    );

    expect($annotation->key)->toBe('api:{ip}');
    expect($annotation->maxAttempts)->toBe(100);
    expect($annotation->decay)->toBe(120);
    expect($annotation->algorithm)->toBe('sliding_window');
    expect($annotation->response)->toBe('Custom message');
    expect($annotation->responseCode)->toBe(503);
});

test('annotation extends abstract annotation', function () {
    $annotation = new RateLimit();

    expect($annotation)->toBeInstanceOf(Hyperf\Di\Annotation\AbstractAnnotation::class);
});
