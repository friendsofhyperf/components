# Model Morph Addon

Model Morph Addon 允許宣告 `morphTo` 關聯的模型自行解析多型別名，而不必只依賴全域性多型對映。

## 要求

- Hyperf 3.2
- `hyperf/database` ~3.2
- `hyperf/di` ~3.2

## 安裝

```shell
composer require friendsofhyperf/model-morph-addon
```

包的 `ConfigProvider` 會自動註冊兩個 AOP 切面。它沒有需要釋出的配置檔案，也沒有可選的整合依賴。

## 定義模型區域性多型對映

在宣告 `morphTo()` 的模型上重寫 `getActualClassNameForMorph()`。每個關聯模型應透過
`getMorphClass()` 返回資料庫中儲存的別名。

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

解析器應為每個輸入返回有效的模型類。未知別名返回原值，可以保留 Hyperf 的預設回退行為。

## 查詢所有多型型別

當 `hasMorph()` 接收到萬用字元型別列表 `['*']` 時，元件也會應用模型區域性解析器。委託給
`hasMorph()` 的方法（例如 `whereHasMorph()` 和 `doesntHaveMorph()`）具有相同行為。

```php
$images = Image::query()
    ->whereHasMorph('imageable', ['*'])
    ->get();
```

對於 `['*']`，Hyperf 會發現模型表中儲存的不同且非空的多型型別值。元件會先透過
`Image::getActualClassNameForMorph()` 解析每個值，再由 Hyperf 構建關聯查詢。

## 行為

- 載入 `Image::imageable` 關聯時，會透過 `Image::getActualClassNameForMorph()` 解析其儲存型別。
- 多型別名屬於宣告 `morphTo()` 的模型；不同模型可以用不同方式解析同一別名。
- 傳給多型查詢方法的顯式型別列表保持 Hyperf 的預設行為。查詢切面只改變完全匹配的萬用字元列表
  `['*']`。
