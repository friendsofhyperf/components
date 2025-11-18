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
use Hyperf\Di\Annotation\MultipleAnnotation;

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
    $annotation1 = new RateLimit(key: 'test:1', maxAttempts: 10, decay: 60);
    $annotation2 = new RateLimit(key: 'test:2', maxAttempts: 20, decay: 120);

    // MultipleAnnotation only takes one annotation in constructor
    $multipleAnnotation = new MultipleAnnotation($annotation1);
    // Insert the second annotation
    $multipleAnnotation->insert($annotation2);

    expect($multipleAnnotation->toAnnotations())->toBeArray();
    expect($multipleAnnotation->toAnnotations())->toHaveCount(2);

    // Verify the annotations are properly converted
    $converted = $multipleAnnotation->toAnnotations();
    expect($converted[0]->key)->toBe('test:1');
    expect($converted[0]->maxAttempts)->toBe(10);
    expect($converted[0]->decay)->toBe(60);

    expect($converted[1]->key)->toBe('test:2');
    expect($converted[1]->maxAttempts)->toBe(20);
    expect($converted[1]->decay)->toBe(120);
});

test('RateLimit aspect can handle single annotation (backward compatibility)', function () {
    $singleAnnotation = new RateLimit(key: 'test:single', maxAttempts: 10);

    $multipleAnnotation = new MultipleAnnotation($singleAnnotation);

    expect($multipleAnnotation)->toBeInstanceOf(MultipleAnnotation::class);

    $converted = $multipleAnnotation->toAnnotations();
    expect($converted)->toBeArray();
    expect($converted)->toHaveCount(1);
    expect($converted[0])->toBeInstanceOf(RateLimit::class);
    expect($converted[0]->key)->toBe('test:single');
    expect($converted[0]->maxAttempts)->toBe(10);
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
