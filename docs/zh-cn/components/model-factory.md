# Model Factory

此组件用于加载 Hyperf 模型工厂定义，并提供
`FriendsOfHyperf\ModelFactory\factory()` 辅助函数。

## 安装

```shell
composer require friendsofhyperf/model-factory --dev
```

此包要求 PHP `^8.0`、Faker，以及 Hyperf 的 config、database 和 stringable 组件，
没有声明可选依赖。

发布配置和示例工厂文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-factory
```

该命令会创建 `config/autoload/model_factory.php` 和 `factories/model_factory.php`。

## 配置

```php
<?php

declare(strict_types=1);

return [
    'path' => BASE_PATH . '/factories/',
];
```

从容器解析 `Hyperf\Database\Model\Factory` 时，会加载配置的目录。仅当目录存在时，
才会加载其中的 PHP 文件。此组件使用 `en_US` 区域设置创建 Faker。

## 定义工厂

配置目录中的文件可以使用 `$factory` 变量：

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

可以向 `define()` 传入第三个参数来定义命名工厂：

```php
$factory->define(User::class, function (Generator $faker) {
    return ['name' => 'Administrator'];
}, 'admin');

$factory->state(User::class, 'suspended', ['active' => false]);
```

## 使用辅助函数

使用前先导入辅助函数：

```php
use App\Model\User;

use function FriendsOfHyperf\ModelFactory\factory;

factory(User::class)->create();
factory(User::class)->create(['name' => 'Admin']);
factory(User::class, 20)->create();
factory(User::class, 'admin')->create();
factory(User::class, 'admin', 5)->create();
```

辅助函数接收模型类，之后可以传入数量，或传入命名工厂及可选数量。
第二个参数为字符串时，它会选择命名工厂，而不是应用工厂状态。
应在返回的构建器上应用已注册的状态：

```php
factory(User::class)->state('suspended')->create();
```

辅助函数返回 `Hyperf\Database\Model\FactoryBuilder`。常用的终止方法包括：

- `make()`：构建模型但不持久化。
- `create()`：构建并持久化模型。
- `raw()`：返回生成的属性数组。

未指定数量时，这些方法返回单个结果；指定数量时，返回模型集合或原始属性数组列表。
传给这些方法的属性数组会覆盖生成的值。如果应用容器不可用，
`factory()` 返回 `null`。
