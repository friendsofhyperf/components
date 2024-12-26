# Validated DTO

## 官方文件

[Laravel Validated DTO 官方文件](https://wendell-adriel.gitbook.io/laravel-validated-dto)

## 安裝

```shell
composer require friendsofhyperf/validated-dto
```

## 建立 DTO

你可以使用 `gen:dto` 命令建立 `DTO`：

```shell
php bin/hyperf.php gen:dto UserDTO
```

`DTO` 將會被建立在 `app/DTO` 目錄下。

## 定義驗證規則

你可以像驗證 `Request` 資料一樣驗證資料：

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

## 建立 DTO 例項

你可以透過多種方式建立 `DTO` 例項：

### 從陣列建立

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);
```

### 從 JSON 字串建立

```php
$dto = UserDTO::fromJson('{"name": "Deeka Wong", "email": "deeka@example.com", "password": "D3Crft!@1b2A"}');
```

### 從請求物件建立

```php
public function store(RequestInterface $request): JsonResponse
{
    $dto = UserDTO::fromRequest($request);
}
```

### 從模型建立

```php
$user = new User([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto = UserDTO::fromModel($user);
```

注意，模型中 `$hidden` 屬性的欄位不會被用於 `DTO`。

### 從 Artisan 命令建立

你有三種方式從 `Artisan Command` 建立 `DTO` 例項：

#### 從命令引數建立

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

#### 從命令選項建立

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

#### 從命令引數和選項建立

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

## 訪問 DTO 資料

建立 `DTO` 例項後，你可以像訪問 `object` 一樣訪問任何屬性：

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

如果你傳遞的屬性不在 `DTO` 的 `rules` 方法中，這些資料將被忽略，並且不會在 `DTO` 中可用：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
    'username' => 'john_doe', 
]);

$dto->username; // 這個屬性在 DTO 中不可用
```

## 定義預設值

有時我們可能有一些可選屬性，並且可以有預設值。你可以在 `defaults` 方法中定義 `DTO` 屬性的預設值：

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

使用上面的 `DTO` 定義，你可以執行：

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->username; // 'deeka_wong'
```

## 轉換 DTO 資料

你可以將你的 DTO 轉換為一些格式：

### 轉換為陣列

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

### 轉換為 JSON 字串

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toJson();
// '{"name":"Deeka Wong","email":"deeka@example.com","password":"D3Crft!@1b2A"}'

$dto->toJson(true); // 你可以這樣呼叫它來美化列印你的 JSON
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

## 自定義錯誤訊息、屬性和異常

你可以透過在 `DTO` 類中實現 `messages` 和 `attributes` 方法來定義自定義訊息和屬性：

```php
/**
 * 定義驗證器錯誤的自定義訊息。
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

## 型別轉換

你可以透過在 `DTO` 中定義 `casts` 方法輕鬆轉換你的 DTO 屬性：

```php
/**
 * 定義 DTO 屬性的型別轉換。
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

## 可用型別

### 陣列

對於 JSON 字串，它將轉換為陣列，對於其他型別，它將包裝在陣列中。

```php
protected function casts(): array
{
    return [
        'property' => new ArrayCast(),
    ];
}
```

### 布林值

對於字串值，這使用 `filter_var` 函式和 `FILTER_VALIDATE_BOOLEAN` 標誌。

```php
protected function casts(): array
{
    return [
        'property' => new BooleanCast(),
    ];
}
```

### Carbon

這接受 `Carbon` 建構函式接受的任何值。如果發現無效值，它將丟擲 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

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

你也可以在定義轉換時傳遞一個格式來用於轉換值。如果屬性的格式與指定的不同，它將丟擲 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### CarbonImmutable

這接受 `CarbonImmutable` 建構函式接受的任何值。如果發現無效值，它將丟擲 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

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

你也可以在定義轉換時傳遞一個格式來用於轉換值。如果屬性的格式與指定的不同，它將丟擲 `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### 集合

對於 JSON 字串，它將首先轉換為陣列，然後包裝到 `Collection` 物件中。

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(),
    ];
}
```

如果你想轉換 `Collection` 中的所有元素，你可以將 `Castable` 傳遞給 `CollectionCast` 建構函式。假設你想將 `Collection` 中的所有專案轉換為整數：

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(new IntegerCast()),
    ];
}
```

這適用於所有 `Castable`，包括 `DTOCast` 和 `ModelCast` 用於巢狀資料。

### DTO

這適用於陣列和 JSON 字串。這將驗證資料併為給定的 DTO 轉換資料。

如果資料對 DTO 無效，這將丟擲 `Hyperf\Validation\ValidationException` 異常。

如果屬性不是有效的陣列或有效的 JSON 字串，這將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

如果傳遞給 `DTOCast` 建構函式的類不是 `ValidatedDTO` 例項，這將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new DTOCast(UserDTO::class),
    ];
}
```

### 浮點數

如果發現非數字值，它將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new FloatCast(),
    ];
}
```

### 整數

如果發現非數字值，它將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new IntegerCast(),
    ];
}
```

### 模型

這適用於陣列和 JSON 字串。

如果屬性不是有效的陣列或有效的 JSON 字串，這將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

如果傳遞給 `ModelCast` 建構函式的類不是 `Model` 例項，這將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new ModelCast(User::class),
    ];
}
```

### 物件

這適用於陣列和 JSON 字串。

如果屬性不是有效的陣列或有效的 JSON 字串，這將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new ObjectCast(),
    ];
}
```

### 字串

如果資料不能轉換為字串，這將丟擲 `FriendsOfHyperf\ValidatedDTO\Exception\CastException` 異常。

```php
protected function casts(): array
{
    return [
        'property' => new StringCast(),
    ];
}
```

## 建立你自己的型別轉換

你可以透過實現 `FriendsOfHyperf\ValidatedDTO\Casting\Castable` 介面輕鬆為你的專案建立新的 `Castable` 型別。這個介面有一個必須實現的方法：

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

假設你的專案中有一個 `URLWrapper` 類，並且你希望在將 URL 傳遞給你的 `DTO` 時，它總是返回一個 `URLWrapper` 例項而不是一個簡單的字串：

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
