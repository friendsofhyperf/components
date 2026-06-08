# Compoships

**Compoships** 讓 Hyperf 的 Model ORM 可以定義基於兩個或更多欄位匹配的模型關聯。
它適用於第三方或既有資料庫結構中，單一外鍵欄位不足以定位關聯記錄的場景。

Compoships 只擴展關聯處理能力；它不會令 Hyperf 模型自身的主鍵變成複合主鍵。

## 問題

Eloquent 風格的關聯通常只會把一個外鍵欄位匹配到一個本地鍵或擁有者鍵欄位。在關聯中
額外加入 `where` 子句並不能代替複合鍵關聯，因為預載入會在單個模型實例可用之前
建立關聯查詢。以下例子中，準備預載入約束時 `$this->team_id` 是 `null`。

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    public function tasks()
    {
        return $this->hasMany(Task::class)->where('team_id', $this->team_id);
    }
}
```

## 安裝

透過 Composer 安裝組件：

```shell
composer require friendsofhyperf/compoships
```

此組件面向 Hyperf 3.2，並依賴組件聲明的 `hyperf/database` 及相關 Hyperf 支援包。
`composer.json` 中的建議包是可選第三方包，並不是 Compoships 關聯所必需的依賴。

## 配置

無需發布配置檔案。組件提供了 Hyperf `ConfigProvider`，但目前返回的是空配置陣列。

## 使用

### 使用 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 類

讓模型繼承 `FriendsOfHyperf\Compoships\Database\Eloquent\Model`，而不是直接繼承
`Hyperf\Database\Model\Model`。這個基礎模型使用了 Compoships trait，同時保留正常
的 Hyperf 模型行為。

```php
namespace App;

use FriendsOfHyperf\Compoships\Database\Eloquent\Model;

class User extends Model
{
}
```

### 使用 `FriendsOfHyperf\Compoships\Compoships` Trait

如果模型必須繼承其他基礎類，可以在模型中使用
`FriendsOfHyperf\Compoships\Compoships` trait。

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class User extends Model
{
    use Compoships;
}
```

當關聯使用鍵名陣列時，被關聯模型也必須接入 Compoships：要麼繼承
`FriendsOfHyperf\Compoships\Database\Eloquent\Model`，要麼使用
`FriendsOfHyperf\Compoships\Compoships` trait。否則定義關聯時會拋出
`FriendsOfHyperf\Compoships\Exceptions\InvalidUsageException`。

## 關聯語法

Compoships 支援在以下關聯方法中使用複合鍵：

- `hasOne($related, $foreignKey = null, $localKey = null)`
- `hasMany($related, $foreignKey = null, $localKey = null)`
- `belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)`

將鍵參數由字串改為陣列即可。陣列順序和元素數量應保持一致，因為值會按陣列下標
逐項匹配。

對 `hasOne` 和 `hasMany` 來說，外鍵陣列表示被關聯模型上的欄位，本地鍵陣列表示目前
模型上的欄位：

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Team extends Model
{
    use Compoships;

    public function latestTask()
    {
        return $this->hasOne(Task::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }
}
```

對 `belongsTo` 來說，外鍵陣列表示目前模型上的欄位，擁有者鍵陣列表示被關聯模型上的
欄位：

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Task extends Model
{
    use Compoships;

    public function team()
    {
        return $this->belongsTo(Team::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }
}
```

## 例子

假設一個任務清單由多個團隊管理，並且每個團隊中每個任務分類都有一名負責用戶：

- 一個任務屬於一個分類。
- 一個任務被分配給一個團隊。
- 一個團隊有多個用戶。
- 一個用戶屬於一個團隊。
- 一個用戶負責一個分類下的任務。

某個任務的負責用戶，就是該任務所屬團隊中負責該任務分類的用戶。

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class User extends Model
{
    use Compoships;

    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

反向關聯使用同一組欄位：

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Task extends Model
{
    use Compoships;

    public function user()
    {
        return $this->belongsTo(
            User::class,
            ['team_id', 'category_id'],
            ['team_id', 'category_id']
        );
    }
}
```

## 行為說明

Compoships 使用自訂查詢構造器，因此預載入可以應用多欄位 `whereIn` 約束，關聯存在性
查詢也可以透過 `whereColumn` 比較多欄位。

對 `hasOne` 和 `hasMany` 來說，`save()` 和 `create()` 會把父模型本地鍵的值按順序
寫入被關聯模型的各個外鍵欄位。對 `belongsTo` 來說，`associate()` 會把擁有者鍵的值
按順序寫入目前模型的各個外鍵欄位。
