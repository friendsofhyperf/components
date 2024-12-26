# Validated DTO

## 官方文檔

[Laravel Validated DTO 官方文檔](https://wendell-adriel.gitbook.io/laravel-validated-dto)

## 安裝

```shell
composer require friendsofhyperf/validated-dto
```

## 創建 DTO

你可以使用 `gen:dto` 命令創建 `DTO`：

```shell
php bin/hyperf.php gen:dto UserDTO
```

`DTO` 將會被創建在 `app/DTO` 目錄下。

## 定義驗證規則

你可以像驗證 `Request` 數據一樣驗證數據：

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

## 創建 DTO 實例

你可以通過多種方式創建 `DTO` 實例：

### 從數組創建

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);
```

### 從 JSON 字符串創建

```php
$dto = UserDTO::fromJson('{"name": "Deeka Wong", "email": "deeka@example.com", "password": "D3Crft!@1b2A"}');
```

### 從請求對象創建

```php
public function store(RequestInterface $request): JsonResponse
{
    $dto = UserDTO::fromRequest($request);
}
```

### 從模型創建

```php
$user = new User([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto = UserDTO::fromModel($user);
```

注意，模型中 `$hidden` 屬性的字段不會被用於 `DTO`。

### 從 Artisan 命令創建

你有三種方式從 `Artisan Command` 創建 `DTO` 實例：

#### 從命令參數創建

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

#### 從命令選項創建

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

#### 從命令參數和選項創建

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

## 訪問 DTO 數據

創建 `DTO` 實例後，你可以像訪問 `object` 一樣訪問任何屬性：

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

如果你傳遞的屬性不在 `DTO` 的 `rules` 方法中，這些數據將被忽略，並且不會在 `DTO` 中可用：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
    'username' => 'john_doe', 
]);

$dto->username; // 這個屬性在 DTO 中不可用
```

## 定義默認值

有時我們可能有一些可選屬性，並且可以有默認值。你可以在 `defaults` 方法中定義 `DTO` 屬性的默認值：

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

使用上面的 `DTO` 定義，你可以運行：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->username; // 'deeka_wong'
```

## 轉換 DTO 數據

你可以將你的 DTO 轉換為一些格式：

### 轉換為數組

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

### 轉換為 JSON 字符串

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toJson();
// '{"name":"Deeka Wong","email":"deeka@example.com","password":"D3Crft!@1b2A"}'

$dto->toJson(true); // 你可以這樣調用它來美化打印你的 JSON
// {
//     "name": "Deeka Wong",
//     "email": "deeka@example.com",
//     "password": "D3Crft!@1b2A"
// }
```

### 轉換為 Eloquent 模型

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

## 自定義錯誤消息、屬性和異常

你可以通過在 `DTO` 類中實現 `messages` 和 `attributes` 方法來定義自定義消息和屬性：

```php
/**
 * 定義驗證器錯誤的自定義消息。
 */
protected function messages() {
    return [];
}

/**
 * 定義驗證器錯誤的自定義屬性。
 */
protected function attributes() {
    return [];
}
```

## 類型轉換

你可以通過在 `DTO` 中定義 `casts` 方法輕鬆轉換你的 DTO 屬性：

```php
/**
 * 定義 DTO 屬性的類型轉換。
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

## 可用類型

### 數組

對於 JSON 字符串，它將轉換為數組，對於其他類型，它將包裝在數組中。

```php
protected function casts(): array
{
    return [
        'property' => new ArrayCast(),
    ];
}
```

### 布爾值

對於字符串值，這使用 `filter_var` 函數和 `FILTER_VALIDATE_BOOLEAN` 標誌。

```php
protected function casts(): array
{
    return [
        'property' => new BooleanCast(),
    ];
}
```

### Carbon

這接受 `Carbon` 構造函數接受的任何值。如果發現無效值，它將拋出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast(),
    ];
}
```

你也可以在定義轉換時傳遞一個時區，如果需要的話，它將在轉換值時使用。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon'),
    ];
}
```

你也可以在定義轉換時傳遞一個格式來用於轉換值。如果屬性的格式與指定的不同，它將拋出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### CarbonImmutable

這接受 `CarbonImmutable` 構造函數接受的任何值。如果發現無效值，它將拋出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast(),
    ];
}
```

你也可以在定義轉換時傳遞一個時區，如果需要的話，它將在轉換值時使用。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon'),
    ];
}
```

你也可以在定義轉換時傳遞一個格式來用於轉換值。如果屬性的格式與指定的不同，它將拋出 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### 集合

對於 JSON 字符串，它將首先轉換為數組，然後包裝到 `Collection` 對象中。

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(),
    ];
}
```

如果你想轉換 `Collection` 中的所有元素，你可以將 `Castable` 傳遞給 `CollectionCast` 構造函數。假設你想將 `Collection` 中的所有項目轉換為整數：

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(new IntegerCast()),
    ];
}
```

這適用於所有 `Castable`，包括 `DTOCast` 和 `ModelCast` 用於嵌套數據。

### DTO

這適用於數組和 JSON 字符串。這將驗證數據併為給定的 DTO 轉換數據。

如果數據對 DTO 無效，這將拋出 `Hyperf\Validation\ValidationException` 異常。

如果屬性不是有效的數組或有效的 JSON 字符串，這將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

如果傳遞給 `DTOCast` 構造函數的類不是 `ValidatedDTO` 實例，這將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new DTOCast(UserDTO::class),
    ];
}
```

### 浮點數

如果發現非數字值，它將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new FloatCast(),
    ];
}
```

### 整數

如果發現非數字值，它將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new IntegerCast(),
    ];
}
```

### 模型

這適用於數組和 JSON 字符串。

如果屬性不是有效的數組或有效的 JSON 字符串，這將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

如果傳遞給 `ModelCast` 構造函數的類不是 `Model` 實例，這將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new ModelCast(User::class),
    ];
}
```

### 對象

這適用於數組和 JSON 字符串。

如果屬性不是有效的數組或有效的 JSON 字符串，這將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new ObjectCast(),
    ];
}
```

### 字符串

如果數據不能轉換為字符串，這將拋出 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new StringCast(),
    ];
}
```

## 創建你自己的類型轉換

你可以通過實現 `FriendsOfHyperf\ValidatedDTO\Casting\Castable` 接口輕鬆為你的項目創建新的 `Castable` 類型。這個接口有一個必須實現的方法：

```php
/**
 * 轉換給定的值。
 *
 * @param  string  $property
 * @param  mixed  $value
 * @return mixed
 */
public function cast(string $property, mixed $value): mixed;
```

假設你的項目中有一個 `URLWrapper` 類，並且你希望在將 URL 傳遞給你的 `DTO` 時，它總是返回一個 `URLWrapper` 實例而不是一個簡單的字符串：

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

然後你可以將其應用到你的 DTO：

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
