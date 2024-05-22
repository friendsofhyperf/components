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

test('test getOrPut', function ($collection) {
    $data = new $collection(['name' => 'taylor', 'email' => 'foo']);

    $this->assertEquals('taylor', $data->getOrPut('name', null));
    $this->assertEquals('foo', $data->getOrPut('email', null));
    $this->assertEquals('male', $data->getOrPut('gender', 'male'));

    $this->assertEquals('taylor', $data->get('name'));
    $this->assertEquals('foo', $data->get('email'));
    $this->assertEquals('male', $data->get('gender'));

    $data = new $collection(['name' => 'taylor', 'email' => 'foo']);

    $this->assertEquals('taylor', $data->getOrPut('name', function () {
        return null;
    }));

    $this->assertEquals('foo', $data->getOrPut('email', function () {
        return null;
    }));

    $this->assertEquals('male', $data->getOrPut('gender', function () {
        return 'male';
    }));

    $this->assertEquals('taylor', $data->get('name'));
    $this->assertEquals('foo', $data->get('email'));
    $this->assertEquals('male', $data->get('gender'));
})->with('collectionClassProvider');

test('test undot', function ($collection) {
    $data = $collection::make([
        'name' => 'Taylor',
        'meta.foo' => 'bar',
        'meta.baz' => 'boom',
        'meta.bam.boom' => 'bip',
    ])->undot();

    $this->assertSame([
        'name' => 'Taylor',
        'meta' => [
            'foo' => 'bar',
            'baz' => 'boom',
            'bam' => [
                'boom' => 'bip',
            ],
        ],
    ], $data->all());

    $data = $collection::make([
        'foo.0' => 'bar',
        'foo.1' => 'baz',
        'foo.baz' => 'boom',
    ])->undot();

    $this->assertSame([
        'foo' => [
            'bar',
            'baz',
            'baz' => 'boom',
        ],
    ], $data->all());
})->with('collectionClassProvider');
