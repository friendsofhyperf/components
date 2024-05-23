<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Collection\Collection;

dataset('collectionClassProvider', [
    [Collection::class],
    // [LazyCollection::class],
]);

test('test isSingle', function ($collection) {
    $data = new $collection(['name' => 'taylor', 'email' => 'foo']);
    expect($data->isSingle())->toBeFalse();
    $data = new $collection(['name' => 'taylor']);
    expect($data->isSingle())->toBeTrue();
})->with('collectionClassProvider');
