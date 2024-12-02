# Model Factory

## Installation

```shell
composer require friendsofhyperf/model-factory --dev
```

Additionally, publish the vendor configuration file to your application (required by dependencies):

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-factory
```

## Usage

- `/factories/user_factory.php`

```php
<?php

declare(strict_types=1);

use App\Model\User;

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->email,
    ];
});
```

- `/seeders/user_seeder.php`

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use App\Model\User;
use function FriendsOfHyperf\ModelFactory\factory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create 1 user with name 'Admin'
        factory(User::class)->create([
            'name' => 'Admin'
        ]);

        // Create 20 random users
        factory(User::class, 20)->create();
    }
}
```
