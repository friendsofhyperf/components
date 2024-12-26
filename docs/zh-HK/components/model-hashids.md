# Model Hashids

使用 hashids 代替 URL 和列表項中的整數 ID 可以更具吸引力和巧妙。更多信息請訪問 [hashids.org](https://hashids.org/)。

這個包通過動態編碼/解碼 hashids 來為 Hyperf 模型添加 hashids，而不是將它們持久化到數據庫中。因此，不需要額外的數據庫列，並且通過在查詢中使用主鍵可以獲得更高的性能。

功能包括：

- 為模型生成 hashids
- 將 hashids 解析為模型
- 能夠為每個模型自定義 hashid 設置
- 使用 hashids 進行路由綁定（可選）

## 安裝

```shell
composer require friendsofhyperf/model-hashids
```

另外，將供應商配置文件發佈到您的應用程序（依賴項所必需的）：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## 設置

基本功能通過使用 `HasHashid` trait 提供，然後可以通過使用 `HashidRouting` 添加基於 hashids 的路由綁定。

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

Class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

### 自定義 Hashid 設置

可以通過重寫 `getHashidsConnection()` 為每個模型自定義 hashids 設置。它必須返回 `config/autoload/hashids.php` 中連接的名稱。

## 使用

### 基礎

```php

// Generating the model hashid based on its key
$item->hashid();

// Equivalent to the above but with the attribute style
$item->hashid;

// Finding a model based on the provided hashid or
// returning null on failure
Item::findByHashid($hashid);

// Finding a model based on the provided hashid or
// throwing a ModelNotFoundException on failure
Item::findByHashidOrFail($hashid);

// Decoding a hashid to its equivalent id 
$item->hashidToId($hashid);

// Encoding an id to its equivalent hashid
$item->idToHashid($id);

// Getting the name of the hashid connection
$item->getHashidsConnection();

```

### 將 hashid 添加到序列化模型

將其設置為默認值：

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;

class Item extends Model
{
    use HasHashid;
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['hashid'];
}

```

將其設置為特定路由：

`return $item->append('hashid')->toJson();`

### 隱式路由綁定

如果您希望使用模型的 hashid 值解析隱式路由綁定，可以在模型中使用 `HashidRouting`。

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

它重寫了 `getRouteKeyName()`、`getRouteKey()` 和 `resolveRouteBindingQuery()` 以使用 hashids 作為路由鍵。

它支持 Laravel 的自定義特定路由鍵的功能。

```php

Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});

```

#### 自定義默認路由鍵名稱

如果您希望默認使用另一個字段解析隱式路由綁定，可以重寫 `getRouteKeyName()` 以返回解析過程中的字段名稱，並在鏈接中返回其值的 `getRouteKey()`。

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getRouteKey()
    {
        return $this->slug;
    }
}

```

您仍然可以為特定路由指定 hashid。

```php

Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});

```

#### 支持 Laravel 的其他隱式路由綁定功能

使用 `HashidRouting` 時，您仍然可以使用軟刪除和子路由綁定。

```php

Route::get('/items/{item}', function (Item $item) {
    return $item;
})->withTrashed();

Route::get('/user/{user}/items/{item}', function (User $user, Item $item) {
    return $item;
})->scopeBindings();

```
