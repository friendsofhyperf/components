# Model Morph Addon

Model Morph Addon 允許宣告 `morphTo` 關聯的模型自行解析多態別名，而不必只依賴全域多態映射。

## 要求

- Hyperf 3.2
- `hyperf/database` ~3.2
- `hyperf/di` ~3.2

## 安裝

```shell
composer require friendsofhyperf/model-morph-addon
```

套件的 `ConfigProvider` 會自動註冊兩個 AOP 切面。它沒有需要發佈的設定檔，也沒有可選的整合依賴。

## 定義模型局部多態映射

在宣告 `morphTo()` 的模型上覆寫 `getActualClassNameForMorph()`。每個關聯模型應透過
`getMorphClass()` 傳回資料庫中儲存的別名。

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

解析器應為每個輸入傳回有效的模型類別。未知別名傳回原值，可以保留 Hyperf 的預設回退行為。

## 查詢所有多態類型

當 `hasMorph()` 接收到萬用字元類型列表 `['*']` 時，組件也會套用模型局部解析器。委派給
`hasMorph()` 的方法（例如 `whereHasMorph()` 和 `doesntHaveMorph()`）具有相同行為。

```php
$images = Image::query()
    ->whereHasMorph('imageable', ['*'])
    ->get();
```

對於 `['*']`，Hyperf 會找出模型資料表中儲存的不同且非空多態類型值。組件會先透過
`Image::getActualClassNameForMorph()` 解析每個值，再由 Hyperf 建立關聯查詢。

## 行為

- 載入 `Image::imageable` 關聯時，會透過 `Image::getActualClassNameForMorph()` 解析其儲存類型。
- 多態別名屬於宣告 `morphTo()` 的模型；不同模型可以用不同方式解析同一別名。
- 傳給多態查詢方法的明確類型列表維持 Hyperf 的預設行為。查詢切面只改變完全符合的萬用字元列表
  `['*']`。
