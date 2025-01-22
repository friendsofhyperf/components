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
use Hyperf\Collection\LazyCollection;

dataset('collectionClassProvider', [
    [Collection::class],
    [LazyCollection::class],
]);

test('test isSingle', function ($collection) {
    if ($collection === LazyCollection::class) {
        $this->markTestSkipped('LazyCollection does not have isSingle method.');
    }

    $data = new $collection(['name' => 'taylor', 'email' => 'foo']);
    expect($data->isSingle())->toBeFalse();
    $data = new $collection(['name' => 'taylor']);
    expect($data->isSingle())->toBeTrue();
})->with('collectionClassProvider');

test('test collapseWithKeys', function ($collection) {
    $data = new $collection([[1 => 'a'], [3 => 'c'], [2 => 'b'], 'drop']);
    $this->assertEquals([1 => 'a', 3 => 'c', 2 => 'b'], $data->collapseWithKeys()->all());
})->with('collectionClassProvider');

test('test collapseWithKeysOnNestedCollections', function ($collection) {
    $data = new $collection([new $collection(['a' => '1a', 'b' => '1b']), new $collection(['b' => '2b', 'c' => '2c']), 'drop']);
    $this->assertEquals(['a' => '1a', 'b' => '2b', 'c' => '2c'], $data->collapseWithKeys()->all());
})->with('collectionClassProvider');

test('test collapseWithKeysOnEmptyCollection', function ($collection) {
    $data = new $collection();
    $this->assertEquals([], $data->collapseWithKeys()->all());
})->with('collectionClassProvider');
