# Model Morph Addon

Model Morph Addon 允許聲明 `morphTo` 關聯的模型自行解析多態別名，而不必只依賴全局多態映射。

## 要求

- Hyperf 3.2
- `hyperf/database` ~3.2
- `hyperf/di` ~3.2

## 安裝

```shell
composer require friendsofhyperf/model-morph-addon
```

包的 `ConfigProvider` 會自動註冊兩個 AOP 切面。它沒有需要發佈的配置文件，也沒有可選的集成依賴。

## 定義模型局部多態映射

在聲明 `morphTo()` 的模型上重寫 `getActualClassNameForMorph()`。每個關聯模型應通過
`getMorphClass()` 返回數據庫中存儲的別名。

```php
namespace App\Model;

use Hyperf\Database\Model\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }

    public static function getActualClassNameForMorph($class)
    {
        $morphMap = [
            'user' => User::class,
            'book' => Book::class,
        ];

        return $morphMap[$class] ?? $class;
    }
}

class Book extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getMorphClass()
    {
        return 'book';
    }
}

class User extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getMorphClass()
    {
        return 'user';
    }
}
```

解析器應為每個輸入返回有效的模型類。未知別名返回原值，可以保留 Hyperf 的默認回退行為。

## 查詢所有多態類型

當 `hasMorph()` 接收到通配符類型列表 `['*']` 時，組件也會應用模型局部解析器。委託給
`hasMorph()` 的方法（例如 `whereHasMorph()` 和 `doesntHaveMorph()`）具有相同行為。

```php
$images = Image::query()
    ->whereHasMorph('imageable', ['*'])
    ->get();
```

對於 `['*']`，Hyperf 會發現模型表中存儲的不同且非空的多態類型值。組件會先通過
`Image::getActualClassNameForMorph()` 解析每個值，再由 Hyperf 構建關聯查詢。

## 行為

- 加載 `Image::imageable` 關聯時，會通過 `Image::getActualClassNameForMorph()` 解析其存儲類型。
- 多態別名屬於聲明 `morphTo()` 的模型；不同模型可以用不同方式解析同一別名。
- 傳給多態查詢方法的顯式類型列表保持 Hyperf 的默認行為。查詢切面只改變完全匹配的通配符列表
  `['*']`。
