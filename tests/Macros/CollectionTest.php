<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Exception\ItemNotFoundException;
use FriendsOfHyperf\Macros\Exception\MultipleItemsFoundException;
use Hyperf\Collection\Collection;

dataset('collectionClassProvider', [
    [Collection::class],
    // [LazyCollection::class],
]);

test('test ensureForScalar', function ($collection) {
    $data = $collection::make([1, 2, 3]);
    $data->ensure('int');

    $data = $collection::make([1, 2, 3, 'foo']);
    $this->expectException(UnexpectedValueException::class);
    $data->ensure('int');
})->with('collectionClassProvider');

test('test ensureForObjects', function ($collection) {
    $data = $collection::make([new stdClass(), new stdClass(), new stdClass()]);
    $data->ensure(stdClass::class);

    $data = $collection::make([new stdClass(), new stdClass(), new stdClass(), $collection]);
    $this->expectException(UnexpectedValueException::class);
    $data->ensure(stdClass::class);
})->with('collectionClassProvider');

test('test firstOrFailReturnsFirstItemInCollection', function ($collection) {
    $collection = new $collection([
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $this->assertSame(['name' => 'foo'], $collection->where('name', 'foo')->firstOrFail());
    $this->assertSame(['name' => 'foo'], $collection->firstOrFail('name', '=', 'foo'));
    $this->assertSame(['name' => 'foo'], $collection->firstOrFail('name', 'foo'));
})->with('collectionClassProvider');

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

test('test intersectUsingWithNull', function ($collection) {
    $collect = new $collection(['green', 'brown', 'blue']);

    $this->assertEquals([], $collect->intersectUsing(null, 'strcasecmp')->all());
})->with('collectionClassProvider');

test('test intersectUsingCollection', function ($collection) {
    $collect = new $collection(['green', 'brown', 'blue']);

    $this->assertEquals(['green', 'brown'], $collect->intersectUsing(new $collection(['GREEN', 'brown', 'yellow']), 'strcasecmp')->all());
})->with('collectionClassProvider');

test('test pipeThrough', function ($collection) {
    $data = new $collection([1, 2, 3]);

    $result = $data->pipeThrough([
        function ($data) {
            return $data->merge([4, 5]);
        },
        function ($data) {
            return $data->sum();
        },
    ]);

    $this->assertEquals(15, $result);
})->with('collectionClassProvider');

test('test soleReturnsFirstItemInCollectionIfOnlyOneExists', function ($collection) {
    $collection = $collection::make([
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $this->assertSame(['name' => 'foo'], $collection->where('name', 'foo')->sole());
    $this->assertSame(['name' => 'foo'], $collection->sole('name', '=', 'foo'));
    $this->assertSame(['name' => 'foo'], $collection->sole('name', 'foo'));
})->with('collectionClassProvider');

test('test soleThrowsExceptionIfNoItemsExist', function ($collection) {
    $this->expectException(ItemNotFoundException::class);

    $collect = new $collection([
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $collect->where('name', 'INVALID')->sole();
})->with('collectionClassProvider');

test('test soleThrowsExceptionIfMoreThanOneItemExists', function ($collection) {
    $this->expectException(MultipleItemsFoundException::class);

    $collect = new $collection([
        ['name' => 'foo'],
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $collect->where('name', 'foo')->sole();
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

test('test value', function ($collection) {
    $c = $collection::make([['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']]);

    $this->assertEquals('Hello', $c->value('name'));
    $this->assertEquals('World', $c->where('id', 2)->value('name'));

    $c = $collection::make([
        ['id' => 1, 'pivot' => ['value' => 'foo']],
        ['id' => 2, 'pivot' => ['value' => 'bar']],
    ]);

    $this->assertEquals(['value' => 'foo'], $c->value('pivot'));
    $this->assertEquals('foo', $c->value('pivot.value'));
    $this->assertEquals('bar', $c->where('id', 2)->value('pivot.value'));
})->with('collectionClassProvider');

test('test whenEmpty', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->whenEmpty(function () {
        throw new Exception('whenEmpty() should not trigger on a collection with items');
    });

    $this->assertSame(['michael', 'tom'], $data->toArray());

    $data = new $collection();

    $data = $data->whenEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame(['adam'], $data->toArray());
})->with('collectionClassProvider');

test('test whenEmptyDefault', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->whenEmpty(function ($data) {
        return $data->concat(['adam']);
    }, function ($data) {
        return $data->concat(['taylor']);
    });

    $this->assertSame(['michael', 'tom', 'taylor'], $data->toArray());
})->with('collectionClassProvider');

test('test whenNotEmpty', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->whenNotEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame(['michael', 'tom', 'adam'], $data->toArray());

    $data = new $collection();

    $data = $data->whenNotEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame([], $data->toArray());
})->with('collectionClassProvider');

test('test whenNotEmptyDefault', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->whenNotEmpty(function ($data) {
        return $data->concat(['adam']);
    }, function ($data) {
        return $data->concat(['taylor']);
    });

    $this->assertSame(['michael', 'tom', 'adam'], $data->toArray());
})->with('collectionClassProvider');

test('test unless', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->unless(false, function ($data) {
        return $data->concat(['caleb']);
    });

    $this->assertSame(['michael', 'tom', 'caleb'], $data->toArray());

    $data = new $collection(['michael', 'tom']);

    $data = $data->unless(true, function ($data) {
        return $data->concat(['caleb']);
    });

    $this->assertSame(['michael', 'tom'], $data->toArray());
})->with('collectionClassProvider');

test('test unlessDefault', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->unless(true, function ($data) {
        return $data->concat(['caleb']);
    }, function ($data) {
        return $data->concat(['taylor']);
    });

    $this->assertSame(['michael', 'tom', 'taylor'], $data->toArray());
})->with('collectionClassProvider');

test('test unlessEmpty', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->unlessEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame(['michael', 'tom', 'adam'], $data->toArray());

    $data = new $collection();

    $data = $data->unlessEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame([], $data->toArray());
})->with('collectionClassProvider');

test('test unlessEmptyDefault', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->unlessEmpty(function ($data) {
        return $data->concat(['adam']);
    }, function ($data) {
        return $data->concat(['taylor']);
    });

    $this->assertSame(['michael', 'tom', 'adam'], $data->toArray());
})->with('collectionClassProvider');

test('test unlessNotEmpty', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->unlessNotEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame(['michael', 'tom'], $data->toArray());

    $data = new $collection();

    $data = $data->unlessNotEmpty(function ($data) {
        return $data->concat(['adam']);
    });

    $this->assertSame(['adam'], $data->toArray());
})->with('collectionClassProvider');

test('test unlessNotEmptyDefault', function ($collection) {
    $data = new $collection(['michael', 'tom']);

    $data = $data->unlessNotEmpty(function ($data) {
        return $data->concat(['adam']);
    }, function ($data) {
        return $data->concat(['taylor']);
    });

    $this->assertSame(['michael', 'tom', 'taylor'], $data->toArray());
})->with('collectionClassProvider');
