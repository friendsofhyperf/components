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
