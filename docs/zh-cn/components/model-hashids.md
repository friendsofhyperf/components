# Model Hashids

Model Hashids 按需编码和解码模型主键。hashid 不会存储到数据库中，因此查询仍然使用模型的主键列。

## 安装

```shell
composer require friendsofhyperf/model-hashids
```

此组件依赖 `hashids/hashids`，以及 Hyperf 3.2 的 config、database 和 stringable 组件。仅在需要
自定义 hashid 设置时发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## 设置

在模型中使用 `HasHashid`。如果隐式路由绑定也需要使用 hashid，再添加 `HashidRouting`：

```php
use FriendsOfHyperf\ModelHashids\Concerns\HasHashid;
use FriendsOfHyperf\ModelHashids\Concerns\HashidRouting;
use Hyperf\Database\Model\Model;

class Item extends Model
{
    use HasHashid;
    use HashidRouting;
}
```

## 配置

发布后的文件为 `config/autoload/hashids.php`。`default` 用于选择连接，每个连接可设置 `salt`、
`length` 和 `alphabet`：

```php
return [
    'default' => 'main',
    'connections' => [
        'main' => [
            'salt' => '',
            'length' => 0,
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],
    ],
];
```

未发布配置时，组件使用 `main` 连接、空 salt、最小长度 `0` 和上面显示的字符表。

如需为某个模型选择其他连接，请覆写受保护的 `getHashidsConnection()` 方法：

```php
class Item extends Model
{
    use HasHashid;

    protected function getHashidsConnection()
    {
        return 'alternative';
    }
}
```

## 使用

### 编码和查询

```php
// Encode the model's primary key.
$item->hashid();

// Access the same value through the hashid accessor.
$item->hashid;

// Encode an ID or decode a valid hashid.
$item->idToHashid($id);
$item->hashidToId($hashid);

// Add a primary-key constraint decoded from the hashid.
Item::query()->byHashid($hashid)->get();

// Return the first matching model, null, or throw ModelNotFoundException.
Item::findByHashid($hashid);
Item::findByHashidOrFail($hashid);
```

`hashidToId()` 返回 `hashids/hashids` 解码出的第一个 ID；请传入使用相同连接设置生成的有效
hashid。

### 序列化 Hashid

`hashid` 访问器不会自动追加。可将它添加到模型的 `$appends` 属性：

```php
class Item extends Model
{
    use HasHashid;

    protected $appends = ['hashid'];
}
```

也可以仅在需要时追加：

```php
return $item->append('hashid')->toJson();
```

### 隐式路由绑定

`HashidRouting` 将模型的 hashid 作为默认路由键，并通过 `byHashid` 解析：

```php
Route::get('/items/{item}', function (Item $item) {
    return $item;
});
```

当路由明确指定其他字段时，解析会交给模型的父类实现：

```php
Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});
```

也可以将其他字段设为默认路由键，同时在特定路由中指定 `hashid`：

```php
class Item extends Model
{
    use HasHashid;
    use HashidRouting;

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

```php
Route::get('/items/{item:hashid}', function (Item $item) {
    return $item;
});
```
