<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Macros\Exceptions\ItemNotFoundException;
use FriendsOfHyperf\Macros\Exceptions\MultipleItemsFoundException;
use Hyperf\Collection\Collection;

use function Hyperf\Collection\collect;

uses()->group('macros', 'collection');

dataset('collectionClassProvider', [
    [Collection::class],
    // [LazyCollection::class],
]);

test('test firstOrFailReturnsFirstItemInCollection', function () {
    $collection = collect([
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $this->assertSame(['name' => 'foo'], $collection->where('name', 'foo')->firstOrFail());
    $this->assertSame(['name' => 'foo'], $collection->firstOrFail('name', '=', 'foo'));
    $this->assertSame(['name' => 'foo'], $collection->firstOrFail('name', 'foo'));
});

test('test getOrPut', function () {
    $data = new Collection(['name' => 'taylor', 'email' => 'foo']);

    $this->assertEquals('taylor', $data->getOrPut('name', null));
    $this->assertEquals('foo', $data->getOrPut('email', null));
    $this->assertEquals('male', $data->getOrPut('gender', 'male'));

    $this->assertEquals('taylor', $data->get('name'));
    $this->assertEquals('foo', $data->get('email'));
    $this->assertEquals('male', $data->get('gender'));

    $data = new Collection(['name' => 'taylor', 'email' => 'foo']);

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
});

test('test hasAny', function () {
    $data = collect(['id' => 1, 'first' => 'Hello', 'second' => 'World']);

    $this->assertTrue($data->hasAny('first'));
    $this->assertFalse($data->hasAny('third'));
    $this->assertTrue($data->hasAny(['first', 'second']));
    $this->assertTrue($data->hasAny(['first', 'fourth']));
    $this->assertFalse($data->hasAny(['third', 'fourth']));
});

test('test intersectUsingWithNull', function ($collection) {
    $collect = new $collection(['green', 'brown', 'blue']);

    $this->assertEquals([], $collect->intersectUsing(null, 'strcasecmp')->all());
})->with('collectionClassProvider');

test('test intersectUsingCollection', function ($collection) {
    $collect = new $collection(['green', 'brown', 'blue']);

    $this->assertEquals(['green', 'brown'], $collect->intersectUsing(new $collection(['GREEN', 'brown', 'yellow']), 'strcasecmp')->all());
})->with('collectionClassProvider');

test('test intersectAssocWithNull', function ($collection) {
    $array1 = new $collection(['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red']);

    $this->assertEquals([], $array1->intersectAssoc(null)->all());
})->with('collectionClassProvider');

test('test intersectAssocCollection', function ($collection) {
    $array1 = new $collection(['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red']);
    $array2 = new $collection(['a' => 'green', 'b' => 'yellow', 'blue', 'red']);

    $this->assertEquals(['a' => 'green'], $array1->intersectAssoc($array2)->all());
})->with('collectionClassProvider');

test('test intersectAssocUsingWithNull', function ($collection) {
    $array1 = new $collection(['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red']);

    $this->assertEquals([], $array1->intersectAssocUsing(null, 'strcasecmp')->all());
})->with('collectionClassProvider');

test('test intersectAssocUsingCollection', function ($collection) {
    $array1 = new $collection(['a' => 'green', 'b' => 'brown', 'c' => 'blue', 'red']);
    $array2 = new $collection(['a' => 'GREEN', 'B' => 'brown', 'yellow', 'red']);

    $this->assertEquals(['b' => 'brown'], $array1->intersectAssocUsing($array2, 'strcasecmp')->all());
})->with('collectionClassProvider');

test('test pipeThrough', function () {
    $data = new Collection([1, 2, 3]);

    $result = $data->pipeThrough([
        function ($data) {
            return $data->merge([4, 5]);
        },
        function ($data) {
            return $data->sum();
        },
    ]);

    $this->assertEquals(15, $result);
});

test('test sliding', function () {
    // Default parameters: $size = 2, $step = 1
    $this->assertSame([], Collection::times(0)->sliding()->toArray());
    $this->assertSame([], Collection::times(1)->sliding()->toArray());
    $this->assertSame([[1, 2]], Collection::times(2)->sliding()->toArray());
    $this->assertSame(
        [[1, 2], [2, 3]],
        Collection::times(3)->sliding()->map->values()->toArray()
    );

    // Custom step: $size = 2, $step = 3
    $this->assertSame([], Collection::times(1)->sliding(2, 3)->toArray());
    $this->assertSame([[1, 2]], Collection::times(2)->sliding(2, 3)->toArray());
    $this->assertSame([[1, 2]], Collection::times(3)->sliding(2, 3)->toArray());
    $this->assertSame([[1, 2]], Collection::times(4)->sliding(2, 3)->toArray());
    $this->assertSame(
        [[1, 2], [4, 5]],
        Collection::times(5)->sliding(2, 3)->map->values()->toArray()
    );

    // Custom size: $size = 3, $step = 1
    $this->assertSame([], Collection::times(2)->sliding(3)->toArray());
    $this->assertSame([[1, 2, 3]], Collection::times(3)->sliding(3)->toArray());
    $this->assertSame(
        [[1, 2, 3], [2, 3, 4]],
        Collection::times(4)->sliding(3)->map->values()->toArray()
    );
    $this->assertSame(
        [[1, 2, 3], [2, 3, 4]],
        Collection::times(4)->sliding(3)->map->values()->toArray()
    );

    // Custom size and custom step: $size = 3, $step = 2
    $this->assertSame([], Collection::times(2)->sliding(3, 2)->toArray());
    $this->assertSame([[1, 2, 3]], Collection::times(3)->sliding(3, 2)->toArray());
    $this->assertSame([[1, 2, 3]], Collection::times(4)->sliding(3, 2)->toArray());
    $this->assertSame(
        [[1, 2, 3], [3, 4, 5]],
        Collection::times(5)->sliding(3, 2)->map->values()->toArray()
    );
    $this->assertSame(
        [[1, 2, 3], [3, 4, 5]],
        Collection::times(6)->sliding(3, 2)->map->values()->toArray()
    );

    // Ensure keys are preserved, and inner chunks are also collections
    $chunks = Collection::times(3)->sliding();

    $this->assertSame([[0 => 1, 1 => 2], [1 => 2, 2 => 3]], $chunks->toArray());

    $this->assertInstanceOf(Collection::class, $chunks);
    $this->assertInstanceOf(Collection::class, $chunks->first());
    $this->assertInstanceOf(Collection::class, $chunks->skip(1)->first());
});

test('test skip', function () {
    $data = collect([1, 2, 3, 4, 5, 6]);

    // Total items to skip is smaller than collection length
    $this->assertSame([5, 6], $data->skip(4)->values()->all());

    // Total items to skip is more than collection length
    $this->assertSame([], $data->skip(10)->values()->all());
});

test('test soleReturnsFirstItemInCollectionIfOnlyOneExists', function () {
    $collection = collect([
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $this->assertSame(['name' => 'foo'], $collection->where('name', 'foo')->sole());
    $this->assertSame(['name' => 'foo'], $collection->sole('name', '=', 'foo'));
    $this->assertSame(['name' => 'foo'], $collection->sole('name', 'foo'));
});

test('test soleThrowsExceptionIfNoItemsExist', function () {
    $this->expectException(ItemNotFoundException::class);

    $collection = collect([
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $collection->where('name', 'INVALID')->sole();
});

test('test soleThrowsExceptionIfMoreThanOneItemExists', function () {
    $this->expectException(MultipleItemsFoundException::class);

    $collection = collect([
        ['name' => 'foo'],
        ['name' => 'foo'],
        ['name' => 'bar'],
    ]);

    $collection->where('name', 'foo')->sole();
});

test('test sortKeysUsing', function () {
    $data = collect(['B' => 'dayle', 'a' => 'taylor']);

    $this->assertSame(['a' => 'taylor', 'B' => 'dayle'], $data->sortKeysUsing('strnatcasecmp')->all());
});

test('test undot', function () {
    $data = collect([
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

    $data = collect([
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
});

test('test value', function () {
    $c = collect([['id' => 1, 'name' => 'Hello'], ['id' => 2, 'name' => 'World']]);

    $this->assertEquals('Hello', $c->value('name'));
    $this->assertEquals('World', $c->where('id', 2)->value('name'));

    $c = collect([
        ['id' => 1, 'pivot' => ['value' => 'foo']],
        ['id' => 2, 'pivot' => ['value' => 'bar']],
    ]);

    $this->assertEquals(['value' => 'foo'], $c->value('pivot'));
    $this->assertEquals('foo', $c->value('pivot.value'));
    $this->assertEquals('bar', $c->where('id', 2)->value('pivot.value'));
});

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
