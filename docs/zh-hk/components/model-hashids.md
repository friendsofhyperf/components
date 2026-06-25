# Model Hashids

Model Hashids 按需編碼和解碼模型主鍵。hashid 不會存儲到數據庫中，因此查詢仍然使用模型的主鍵列。

## 安裝

```shell
composer require friendsofhyperf/model-hashids
```

此組件依賴 `hashids/hashids`，以及 Hyperf 3.2 的 config、database 和 stringable 組件。僅在需要
自定義 hashid 設置時發佈配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## 設置

在模型中使用 `HasHashid`。如果隱式路由綁定也需要使用 hashid，再添加 `HashidRouting`：

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

發佈後的文件為 `config/autoload/hashids.php`。`default` 用於選擇連接，每個連接可設置 `salt`、
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

未發佈配置時，組件使用 `main` 連接、空 salt、最小長度 `0` 和上面顯示的字符表。

如需為某個模型選擇其他連接，請覆寫受保護的 `getHashidsConnection()` 方法：

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

### 編碼和查詢

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

`hashidToId()` 返回 `hashids/hashids` 解碼出的第一個 ID；請傳入使用相同連接設置生成的有效
hashid。

### 序列化 Hashid

`hashid` 訪問器不會自動追加。可將它添加到模型的 `$appends` 屬性：

```php
class Item extends Model
{
    use HasHashid;

    protected $appends = ['hashid'];
}
```

也可以僅在需要時追加：

```php
return $item->append('hashid')->toJson();
```

### 隱式路由綁定

`HashidRouting` 將模型的 hashid 作為默認路由鍵，並通過 `byHashid` 解析：

```php
Route::get('/items/{item}', function (Item $item) {
    return $item;
});
```

當路由明確指定其他字段時，解析會交給模型的父類實現：

```php
Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});
```

也可以將其他字段設為默認路由鍵，同時在特定路由中指定 `hashid`：

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
