# model-factory

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/model-factory)](https://packagist.org/packages/friendsofhyperf/model-factory)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/model-factory)](https://packagist.org/packages/friendsofhyperf/model-factory)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/model-factory)](https://github.com/friendsofhyperf/model-factory)

## Installation

Install the package with Composer:

```shell
composer require friendsofhyperf/model-factory --dev
```

Also, publish the vendor config files to your application (necessary for the dependencies):

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-factory
```

## Example usage

`/factories/user_factory.php`

```php
<?php


declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use App\Model\User;


$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->email,
    ];
});
```

`/seeders/user_seeder.php`

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

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
