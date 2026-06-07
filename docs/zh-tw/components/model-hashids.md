# Model Hashids

Model Hashids 按需編碼和解碼模型主鍵。hashid 不會儲存到資料庫中，因此查詢仍然使用模型的主鍵欄位。

## 安裝

```shell
composer require friendsofhyperf/model-hashids
```

此元件依賴 `hashids/hashids`，以及 Hyperf 3.2 的 config、database 和 stringable 元件。僅在需要
自訂 hashid 設定時發布設定檔：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/model-hashids
```

## 設定

在模型中使用 `HasHashid`。如果隱式路由繫結也需要使用 hashid，再新增 `HashidRouting`：

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

發布後的檔案為 `config/autoload/hashids.php`。`default` 用於選擇連線，每個連線可設定 `salt`、
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

未發布設定時，元件使用 `main` 連線、空 salt、最小長度 `0` 和上面顯示的字元表。

如需為某個模型選擇其他連線，請覆寫受保護的 `getHashidsConnection()` 方法：

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

`hashidToId()` 回傳 `hashids/hashids` 解碼出的第一個 ID；請傳入使用相同連線設定產生的有效
hashid。

### 序列化 Hashid

`hashid` 存取器不會自動附加。可將它新增到模型的 `$appends` 屬性：

```php
class Item extends Model
{
    use HasHashid;

    protected $appends = ['hashid'];
}
```

也可以僅在需要時附加：

```php
return $item->append('hashid')->toJson();
```

### 隱式路由繫結

`HashidRouting` 將模型的 hashid 作為預設路由鍵，並透過 `byHashid` 解析：

```php
Route::get('/items/{item}', function (Item $item) {
    return $item;
});
```

當路由明確指定其他欄位時，解析會交給模型的父類別實作：

```php
Route::get('/items/{item:slug}', function (Item $item) {
    return $item;
});
```

也可以將其他欄位設為預設路由鍵，同時在特定路由中指定 `hashid`：

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
