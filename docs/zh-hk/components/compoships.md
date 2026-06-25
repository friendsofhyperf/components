# Compoships

**Compoships** 允許 Hyperf 的 Model ORM 定義基於兩個或更多列匹配的模型關係。
它適用於第三方或遺留數據庫結構中單個外鍵列不足以定位關聯記錄的場景。

Compoships 只擴展關係處理能力；它不會讓 Hyperf 模型自身的主鍵變成複合主鍵。

## 問題

Eloquent 風格的關係通常只會把一個外鍵列匹配到一個本地鍵或擁有者鍵列。在關係中
額外添加 `where` 子句並不能替代複合鍵關係，因為預加載會在單個模型實例可用之前
構造關係查詢。下面的示例中，準備預加載約束時 `$this->team_id` 是 `null`。

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

通過 Composer 安裝組件：

```shell
composer require friendsofhyperf/compoships
```

該組件面向 Hyperf 3.2，並依賴組件聲明的 `hyperf/database` 及相關 Hyperf 支撐包。
`composer.json` 中的建議包是可選第三方包，並不是 Compoships 關係所必需的依賴。

## 配置

無需發佈配置文件。組件提供了 Hyperf `ConfigProvider`，但當前返回的是空配置數組。

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

當關系使用鍵名數組時，被關聯模型也必須接入 Compoships：要麼繼承
`FriendsOfHyperf\Compoships\Database\Eloquent\Model`，要麼使用
`FriendsOfHyperf\Compoships\Compoships` trait。否則定義關係時會拋出
`FriendsOfHyperf\Compoships\Exceptions\InvalidUsageException`。

## 關係語法

Compoships 支持在以下關係方法中使用複合鍵：

- `hasOne($related, $foreignKey = null, $localKey = null)`
- `hasMany($related, $foreignKey = null, $localKey = null)`
- `belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)`

將鍵參數從字符串改為數組即可。數組順序和元素數量應保持一致，因為值會按數組下標
逐項匹配。

對 `hasOne` 和 `hasMany` 來説，外鍵數組表示被關聯模型上的列，本地鍵數組表示當前
模型上的列：

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

對 `belongsTo` 來説，外鍵數組表示當前模型上的列，擁有者鍵數組表示被關聯模型上的
列：

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

## 示例

假設一個任務列表由多個團隊管理，並且每個團隊中每個任務分類都有一名負責用户：

- 一個任務屬於一個分類。
- 一個任務被分配給一個團隊。
- 一個團隊有多個用户。
- 一個用户屬於一個團隊。
- 一個用户負責一個分類下的任務。

某個任務的負責用户，就是該任務所屬團隊中負責該任務分類的用户。

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

反向關係使用同一組列：

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

## 行為説明

Compoships 使用自定義查詢構造器，因此預加載可以應用多列 `whereIn` 約束，關係存在性
查詢也可以通過 `whereColumn` 比較多列。

對 `hasOne` 和 `hasMany` 來説，`save()` 和 `create()` 會把父模型本地鍵的值按順序
寫入被關聯模型的各個外鍵列。對 `belongsTo` 來説，`associate()` 會把擁有者鍵的值
按順序寫入當前模型的各個外鍵列。
