# Compoships

**Compoships** 提供了在 Hyperf 的 Model ORM 中基於兩個（或更多）列指定關係的能力。當處理第三方或預先存在的模式/數據庫時，通常會出現需要在 Eloquent 關係的定義中匹配多個列的情況。

## 問題

Eloquent 不支持複合鍵。因此，無法通過匹配多個列來定義從一個模型到另一個模型的關係。嘗試使用 `where` 子句（如下例所示）在預加載關係時不起作用，因為在處理關係時 **$this->team_id** 為 null。

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    public function tasks()
    {
        //WON'T WORK WITH EAGER LOADING!!!
        return $this->hasMany(Task::class)->where('team_id', $this->team_id);
    }
}
```

## 安裝

推薦通過 [Composer](http://getcomposer.org/) 安裝 **Compoships** 組件。

```shell
composer require friendsofhyperf/compoships
```

## 使用

### 使用 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 類

只需讓您的模型類派生自 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 基類。`FriendsOfHyperf\Compoships\Database\Eloquent\Model` 擴展了 `Eloquent` 基類，而不改變其核心功能。

### 使用 `FriendsOfHyperf\Compoships\Compoships` trait

如果由於某些原因您無法從 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 派生您的模型，您可以利用 `FriendsOfHyperf\Compoships\Compoships` trait。只需在您的模型中使用該 trait。

**注意：** 要從模型 *A* 到另一個模型 *B* 定義多列關係，**兩個模型都必須擴展 `FriendsOfHyperf\Compoships\Database\Eloquent\Model` 或使用 `FriendsOfHyperf\Compoships\Compoships` trait**

### 用法

... 現在我們可以通過匹配兩個或更多列（通過傳遞列數組而不是字符串）來定義從模型 *A* 到另一個模型 *B* 的關係。

```php
namespace App;

use Hyperf\Database\Model\Model;

class A extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function b()
    {
        return $this->hasMany('B', ['foreignKey1', 'foreignKey2'], ['localKey1', 'localKey2']);
    }
}
```

我們可以使用相同的語法來定義關係的反向關係：

```php
namespace App;

use Hyperf\Database\Model\Model;

class B extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function a()
    {
        return $this->belongsTo('A', ['foreignKey1', 'foreignKey2'], ['ownerKey1', 'ownerKey2']);
    }
}
```

### 例子

作為一個例子，假設我們有一個帶有分類的任務列表，由多個用户團隊管理，其中：

- 一個任務屬於一個分類
- 一個任務被分配給一個團隊
- 一個團隊有很多用户
- 一個用户屬於一個團隊
- 一個用户負責一個分類的任務

負責特定任務的用户是當前負責團隊內該分類的用户。

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

同樣的語法可以定義關係的反向關係：

```php
namespace App;

use Hyperf\Database\Model\Model;

class Task extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function user()
    {
        return $this->belongsTo(User::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```
