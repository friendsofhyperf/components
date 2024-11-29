# Validated DTO

## 官方文档

https://wendell-adriel.gitbook.io/laravel-validated-dto

## 安装

```shell
composer require friendsofhyperf/validated-dto
```

## 创建 DTO

你可以使用 `gen:dto` 命令创建 `DTO`：

```shell
php bin/hyperf.php gen:dto UserDTO
```

`DTO` 将会被创建在 `app/DTO` 目录下。

## 定义验证规则

你可以像验证 `Request` 数据一样验证数据：

```php
<?php

namespace App\DTO;

class UserDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
```

## 创建 DTO 实例

你可以通过多种方式创建 `DTO` 实例：

### 从数组创建

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);
```

### 从 JSON 字符串创建

```php
$dto = UserDTO::fromJson('{"name": "Deeka Wong", "email": "deeka@example.com", "password": "D3Crft!@1b2A"}');
```

### 从请求对象创建

```php
public function store(RequestInterface $request): JsonResponse
{
    $dto = UserDTO::fromRequest($request);
}
```

### 从模型创建

```php
$user = new User([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto = UserDTO::fromModel($user);
```

注意，模型中 `$hidden` 属性的字段不会被用于 `DTO`。

### 从 Artisan 命令创建

你有三种方式从 `Artisan Command` 创建 `DTO` 实例：

#### 从命令参数创建

```php
<?php

use App\DTO\UserDTO;
use Hyperf\Command\Command;

class CreateUserCommand extends Command
{
    protected ?string $signature = 'create:user {name} {email} {password}';

    protected string $description = 'Create a new User';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws ValidationException
     */
    public function handle()
    {
        $dto = UserDTO::fromCommandArguments($this);
    }
}
```

#### 从命令选项创建

```php
<?php

use App\DTO\UserDTO;
use Hyperf\Command\Command;

class CreateUserCommand extends Command
{
    protected ?string $signature = 'create:user { --name= : The user name } { --email= : The user email } { --password= : The user password }';

    protected string $description = 'Create a new User';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws ValidationException
     */
    public function handle()
    {
        $dto = UserDTO::fromCommandOptions($this);
    }
}
```

#### 从命令参数和选项创建

```php
<?php

use App\DTO\UserDTO;
use Hyperf\Command\Command;

class CreateUserCommand extends Command
{
    protected ?string $signature = 'create:user {name}
                                        { --email= : The user email }
                                        { --password= : The user password }';

    protected string $description = 'Create a new User';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws ValidationException
     */
    public function handle()
    {
        $dto = UserDTO::fromCommand($this);
    }
}
```

## 访问 DTO 数据

创建 `DTO` 实例后，你可以像访问 `object` 一样访问任何属性：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->name; // 'Deeka Wong'
$dto->email; // 'deeka@example.com'
$dto->password; // 'D3Crft!@1b2A'
```

如果你传递的属性不在 `DTO` 的 `rules` 方法中，这些数据将被忽略，并且不会在 `DTO` 中可用：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
    'username' => 'john_doe', 
]);

$dto->username; // 这个属性在 DTO 中不可用
```

## 定义默认值

有时我们可能有一些可选属性，并且可以有默认值。你可以在 `defaults` 方法中定义 `DTO` 属性的默认值：

```php
<?php

namespace App\DTO;

use Hyperf\Stringable\Str;

class UserDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email'],
            'username' => ['sometimes', 'string'],
            'password' => ['required'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'username' => Str::snake($this->name),
        ];
    }
}
```

使用上面的 `DTO` 定义，你可以运行：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->username; // 'deeka_wong'
```

## 转换 DTO 数据

你可以将你的 DTO 转换为一些格式：

### 转换为数组

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toArray();
// [
//     "name" => "Deeka Wong",
//     "email" => "deeka@example.com",
//     "password" => "D3Crft!@1b2A",
// ]
```

### 转换为 JSON 字符串

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toJson();
// '{"name":"Deeka Wong","email":"deeka@example.com","password":"D3Crft!@1b2A"}'

$dto->toJson(true); // 你可以这样调用它来美化打印你的 JSON
// {
//     "name": "Deeka Wong",
//     "email": "deeka@example.com",
//     "password": "D3Crft!@1b2A"
// }
```

### 转换为 Eloquent 模型

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toModel(\App\Model\User::class);
// App\Model\User {#3776
//     name: "Deeka Wong",
//     email: "deeka@example.com",
//     password: "D3Crft!@1b2A",
// }

```

## 自定义错误消息、属性和异常

你可以通过在 `DTO` 类中实现 `messages` 和 `attributes` 方法来定义自定义消息和属性：

```php
/**
 * 定义验证器错误的自定义消息。
 */
protected function messages() {
    return [];
}

/**
 * 定义验证器错误的自定义属性。
 */
protected function attributes() {
    return [];
}
```

## 类型转换

你可以通过在 `DTO` 中定义 `casts` 方法轻松转换你的 DTO 属性：

```php
/**
 * 定义 DTO 属性的类型转换。
 *
 * @return array
 */
protected function casts(): array
{
    return [
        'name' => new StringCast(),
        'age'  => new IntegerCast(),
        'created_at' => new CarbonImmutableCast(),
    ];
}
```

## 可用类型

### 数组

对于 JSON 字符串，它将转换为数组，对于其他类型，它将包装在数组中。

```php
protected function casts(): array
{
    return [
        'property' => new ArrayCast(),
    ];
}
```

### 布尔值

对于字符串值，这使用 `filter_var` 函数和 `FILTER_VALIDATE_BOOLEAN` 标志。

```php
protected function casts(): array
{
    return [
        'property' => new BooleanCast(),
    ];
}
```

### Carbon

这接受 `Carbon` 构造函数接受的任何值。如果发现无效值，它将抛出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast(),
    ];
}
```

你也可以在定义转换时传递一个时区，如果需要的话，它将在转换值时使用。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon'),
    ];
}
```

你也可以在定义转换时传递一个格式来用于转换值。如果属性的格式与指定的不同，它将抛出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### CarbonImmutable

这接受 `CarbonImmutable` 构造函数接受的任何值。如果发现无效值，它将抛出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast(),
    ];
}
```

你也可以在定义转换时传递一个时区，如果需要的话，它将在转换值时使用。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon'),
    ];
}
```

你也可以在定义转换时传递一个格式来用于转换值。如果属性的格式与指定的不同，它将抛出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### 集合

对于 JSON 字符串，它将首先转换为数组，然后包装到 `Collection` 对象中。

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(),
    ];
}
```

如果你想转换 `Collection` 中的所有元素，你可以将 `Castable` 传递给 `CollectionCast` 构造函数。假设你想将 `Collection` 中的所有项目转换为整数：

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(new IntegerCast()),
    ];
}
```

这适用于所有 `Castable`，包括 `DTOCast` 和 `ModelCast` 用于嵌套数据。

### DTO

这适用于数组和 JSON 字符串。这将验证数据并为给定的 DTO 转换数据。

如果数据对 DTO 无效，这将抛出 `Hyperf\Validation\ValidationException` 异常。

如果属性不是有效的数组或有效的 JSON 字符串，这将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

如果传递给 `DTOCast` 构造函数的类不是 `ValidatedDTO` 实例，这将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new DTOCast(UserDTO::class),
    ];
}
```

### 浮点数

如果发现非数字值，它将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new FloatCast(),
    ];
}
```

### 整数

如果发现非数字值，它将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new IntegerCast(),
    ];
}
```

### 模型

这适用于数组和 JSON 字符串。

如果属性不是有效的数组或有效的 JSON 字符串，这将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

如果传递给 `ModelCast` 构造函数的类不是 `Model` 实例，这将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new ModelCast(User::class),
    ];
}
```

### 对象

这适用于数组和 JSON 字符串。

如果属性不是有效的数组或有效的 JSON 字符串，这将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new ObjectCast(),
    ];
}
```

### 字符串

如果数据不能转换为字符串，这将抛出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 异常。

```php
protected function casts(): array
{
    return [
        'property' => new StringCast(),
    ];
}
```

## 创建你自己的类型转换

你可以通过实现 `FriendsOfHyperf\ValidatedDTO\Casting\Castable` 接口轻松为你的项目创建新的 `Castable` 类型。这个接口有一个必须实现的方法：

```php
/**
 * 转换给定的值。
 *
 * @param  string  $property
 * @param  mixed  $value
 * @return mixed
 */
public function cast(string $property, mixed $value): mixed;
```

假设你的项目中有一个 `URLWrapper` 类，并且你希望在将 URL 传递给你的 `DTO` 时，它总是返回一个 `URLWrapper` 实例而不是一个简单的字符串：

```php
class URLCast implements Castable
{
    /**
     * @param  string  $property
     * @param  mixed  $value
     * @return URLWrapper
     */
    public function cast(string $property, mixed $value): URLWrapper
    {
        return new URLWrapper($value);
    }
}
```

然后你可以将其应用到你的 DTO：

```php
class CustomDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'url' => ['required', 'url'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'url' => new URLCast(),
        ];
    }
}
```
