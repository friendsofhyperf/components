# Model Factory

This component loads Hyperf model factory definitions and provides the
`FriendsOfHyperf\ModelFactory\factory()` helper.

## Installation

```shell
composer require friendsofhyperf/model-factory --dev
```

The package requires PHP `^8.0`, Faker, and the Hyperf config, database, and stringable
components. It does not declare optional dependencies.

Publish the config and example factory files:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-factory
```

This creates `config/autoload/model_factory.php` and `factories/model_factory.php`.

## Configuration

```php
<?php

declare(strict_types=1);

return [
    'path' => BASE_PATH . '/factories/',
];
```

The configured directory is loaded when `Hyperf\Database\Model\Factory` is resolved from
the container. Only PHP files in an existing directory are loaded. The component creates
Faker with the `en_US` locale.

## Defining Factories

Files in the configured directory receive a `$factory` variable:

```php
<?php

declare(strict_types=1);

use App\Model\User;
use Faker\Generator;

$factory->define(User::class, function (Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
    ];
});
```

You may provide a third argument to `define()` for a named definition:

```php
$factory->define(User::class, function (Generator $faker) {
    return ['name' => 'Administrator'];
}, 'admin');

$factory->state(User::class, 'suspended', ['active' => false]);
```

## Using the Helper

Import the helper before using it:

```php
use App\Model\User;

use function FriendsOfHyperf\ModelFactory\factory;

factory(User::class)->create();
factory(User::class)->create(['name' => 'Admin']);
factory(User::class, 20)->create();
factory(User::class, 'admin')->create();
factory(User::class, 'admin', 5)->create();
```

The helper accepts a model class, followed by either a count or a named definition and
an optional count. A string second argument selects a named definition; it does not apply
a factory state. Apply registered states on the returned builder:

```php
factory(User::class)->state('suspended')->create();
```

The helper returns a `Hyperf\Database\Model\FactoryBuilder`. Its commonly used terminal
methods are:

- `make()` builds models without persisting them.
- `create()` builds and persists models.
- `raw()` returns generated attribute arrays.

With no count, these methods return one result. With a count, they return a model
collection or an array of raw attributes. Attribute arrays passed to these methods
override generated values. If no application container is available, `factory()` returns
`null`.
