# Model Factory

此元件用於載入 Hyperf 模型工廠定義，並提供
`FriendsOfHyperf\ModelFactory\factory()` 輔助函式。

## 安裝

```shell
composer require friendsofhyperf/model-factory --dev
```

此包要求 PHP `^8.0`、Faker，以及 Hyperf 的 config、database 和 stringable 元件，
沒有宣告可選依賴。

釋出配置和示例工廠檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-factory
```

該命令會建立 `config/autoload/model_factory.php` 和 `factories/model_factory.php`。

## 配置

```php
<?php

declare(strict_types=1);

return [
    'path' => BASE_PATH . '/factories/',
];
```

從容器解析 `Hyperf\Database\Model\Factory` 時，會載入配置的目錄。僅當目錄存在時，
才會載入其中的 PHP 檔案。此元件使用 `en_US` 區域設定建立 Faker。

## 定義工廠

配置目錄中的檔案可以使用 `$factory` 變數：

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

可以向 `define()` 傳入第三個引數來定義命名工廠：

```php
$factory->define(User::class, function (Generator $faker) {
    return ['name' => 'Administrator'];
}, 'admin');

$factory->state(User::class, 'suspended', ['active' => false]);
```

## 使用輔助函式

使用前先匯入輔助函式：

```php
use App\Model\User;

use function FriendsOfHyperf\ModelFactory\factory;

factory(User::class)->create();
factory(User::class)->create(['name' => 'Admin']);
factory(User::class, 20)->create();
factory(User::class, 'admin')->create();
factory(User::class, 'admin', 5)->create();
```

輔助函式接收模型類，之後可以傳入數量，或傳入命名工廠及可選數量。
第二個引數為字串時，它會選擇命名工廠，而不是應用工廠狀態。
應在返回的構建器上應用已註冊的狀態：

```php
factory(User::class)->state('suspended')->create();
```

輔助函式返回 `Hyperf\Database\Model\FactoryBuilder`。常用的終止方法包括：

- `make()`：構建模型但不持久化。
- `create()`：構建並持久化模型。
- `raw()`：返回生成的屬性陣列。

未指定數量時，這些方法返回單個結果；指定數量時，返回模型集合或原始屬性陣列列表。
傳給這些方法的屬性陣列會覆蓋生成的值。如果應用容器不可用，
`factory()` 返回 `null`。
