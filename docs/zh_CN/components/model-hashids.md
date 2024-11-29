# Model Hashids

使用 hashids 代替 URL 和列表项中的整数 ID 可以更具吸引力和巧妙。更多信息请访问 [hashids.org](https://hashids.org/)。

这个包通过动态编码/解码 hashids 来为 Hyperf 模型添加 hashids，而不是将它们持久化到数据库中。因此，不需要额外的数据库列，并且通过在查询中使用主键可以获得更高的性能。

功能包括：

- 为模型生成 hashids
- 将 hashids 解析为模型
- 能够为每个模型自定义 hashid 设置
- 使用 hashids 进行路由绑定（可选）

## 安装

```shell
composer require friendsofhyperf/model-hashids
```

另外，将供应商配置文件发布到您的应用程序（依赖项所必需的）：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## 设置

基本功能通过使用 `HasHashid` trait 提供，然后可以通过使用 `HashidRouting` 添加基于 hashids 的路由绑定。

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

Class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

### 自定义 Hashid 设置

可以通过重写 `getHashidsConnection()` 为每个模型自定义 hashids 设置。它必须返回 `config/autoload/hashids.php` 中连接的名称。

## 使用

### 基础

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

### 将 hashid 添加到序列化模型

将其设置为默认值：

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

将其设置为特定路由：

`return $item->append('hashid')->toJson();`

### 隐式路由绑定

如果您希望使用模型的 hashid 值解析隐式路由绑定，可以在模型中使用 `HashidRouting`。

```php

use Hyperf\Database\Model\Model;
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;

class Item extends Model
{
    use HasHashid, HashidRouting;
}

```

它重写了 `getRouteKeyName()`、`getRouteKey()` 和 `resolveRouteBindingQuery()` 以使用 hashids 作为路由键。

它支持 Laravel 的自定义特定路由键的功能。

```php

Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});

```

#### 自定义默认路由键名称

如果您希望默认使用另一个字段解析隐式路由绑定，可以重写 `getRouteKeyName()` 以返回解析过程中的字段名称，并在链接中返回其值的 `getRouteKey()`。

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

您仍然可以为特定路由指定 hashid。

```php

Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});

```

#### 支持 Laravel 的其他隐式路由绑定功能

使用 `HashidRouting` 时，您仍然可以使用软删除和子路由绑定。

```php

Route::get('/items/{item}', function (Item $item) {
    return $item;
})->withTrashed();

Route::get('/user/{user}/items/{item}', function (User $user, Item $item) {
    return $item;
})->scopeBindings();

```
