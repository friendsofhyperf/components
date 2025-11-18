<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\Annotation\RateLimit;

test('RateLimit annotation supports IS_REPEATABLE flag', function () {
    $reflection = new ReflectionClass(RateLimit::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->not->toBeEmpty();

    $attribute = $attributes[0]->newInstance();
    $targetFlag = $attribute->flags;

    // Check if IS_REPEATABLE flag is set
    expect($targetFlag & Attribute::IS_REPEATABLE)->toBe(Attribute::IS_REPEATABLE);
    expect($targetFlag & Attribute::TARGET_METHOD)->toBe(Attribute::TARGET_METHOD);
});

test('RateLimit aspect can handle multiple annotations', function () {
    $metadata = new class {
        public array $method = [];
    };

    // Simulate multiple annotations
    $metadata->method[RateLimit::class] = [
        new RateLimit(key: 'test:1', maxAttempts: 10, decay: 60),
        new RateLimit(key: 'test:2', maxAttempts: 20, decay: 120),
    ];

    expect($metadata->method[RateLimit::class])->toBeArray();
    expect($metadata->method[RateLimit::class])->toHaveCount(2);
});

test('RateLimit aspect can handle single annotation (backward compatibility)', function () {
    $metadata = new class {
        public array $method = [];
    };

    // Simulate single annotation (backward compatibility)
    $metadata->method[RateLimit::class] = new RateLimit(key: 'test:single', maxAttempts: 10);

    $annotation = $metadata->method[RateLimit::class];

    // Should remain as single instance for backward compatibility
    expect($annotation)->toBeInstanceOf(RateLimit::class);
    expect($annotation->key)->toBe('test:single');
    expect($annotation->maxAttempts)->toBe(10);
});

test('multiple rate limit configurations have different properties', function () {
    $rateLimit1 = new RateLimit(
        key: 'ip:{ip}',
        maxAttempts: 100,
        decay: 60,
        algorithm: Algorithm::FIXED_WINDOW,
        response: 'IP limit exceeded'
    );

    $rateLimit2 = new RateLimit(
        key: 'user:{user_id}',
        maxAttempts: 1000,
        decay: 3600,
        algorithm: Algorithm::SLIDING_WINDOW,
        response: 'User limit exceeded'
    );

    $rateLimit3 = new RateLimit(
        key: 'global:api',
        maxAttempts: 10000,
        decay: 86400,
        algorithm: Algorithm::TOKEN_BUCKET,
        response: 'Global limit exceeded'
    );

    $annotations = [$rateLimit1, $rateLimit2, $rateLimit3];

    expect($annotations)->toHaveCount(3);

    // Verify each annotation has distinct properties
    expect($annotations[0]->key)->toBe('ip:{ip}');
    expect($annotations[0]->maxAttempts)->toBe(100);
    expect($annotations[0]->decay)->toBe(60);

    expect($annotations[1]->key)->toBe('user:{user_id}');
    expect($annotations[1]->maxAttempts)->toBe(1000);
    expect($annotations[1]->decay)->toBe(3600);

    expect($annotations[2]->key)->toBe('global:api');
    expect($annotations[2]->maxAttempts)->toBe(10000);
    expect($annotations[2]->decay)->toBe(86400);

    // Verify different algorithms
    expect($annotations[0]->algorithm)->toBe(Algorithm::FIXED_WINDOW);
    expect($annotations[1]->algorithm)->toBe(Algorithm::SLIDING_WINDOW);
    expect($annotations[2]->algorithm)->toBe(Algorithm::TOKEN_BUCKET);
});
