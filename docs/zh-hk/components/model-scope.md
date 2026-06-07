# Model Scope

Model Scope 組件透過可重複使用的 `#[ScopedBy]` 註解註冊 Hyperf 模型全域
Scope。

## 使用要求

- Hyperf 3.2
- `hyperf/database` ~3.2；使用模型 Scope 時必須安裝，但本組件僅將其聲明為
  Composer 建議依賴

## 安裝

```shell
composer require friendsofhyperf/model-scope
```

組件的 `ConfigProvider` 會自動註冊 Scope 監聽器，無需發佈配置檔案。

## 定義 Scope

每個 Scope 都必須實現 `Hyperf\Database\Model\Scope`。

```php
namespace App\Model\Scope;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;

class AncientScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('created_at', '<', now()->subYears(2000));
    }
}
```

## 將 Scope 綁定到模型

在模型類別上加入 `#[ScopedBy]`。應用程式啟動時，該 Scope 會被註冊為全域
Scope。

```php
namespace App\Model;

use App\Model\Scope\AncientScope;
use FriendsOfHyperf\ModelScope\Annotation\ScopedBy;
use Hyperf\Database\Model\Model;

#[ScopedBy(AncientScope::class)]
class User extends Model
{
}
```

## 註冊多個 Scope

`classes` 參數可以接收單個 Scope 類別或 Scope 類別陣列，該註解也可以重複
使用。`priority` 預設為 `0`；優先級越高的 Scope 越先註冊。同一個註解中
傳入的所有類別共用一個優先級。

```php
namespace App\Model;

use App\Model\Scope\ActiveScope;
use App\Model\Scope\AncientScope;
use App\Model\Scope\TenantScope;
use FriendsOfHyperf\ModelScope\Annotation\ScopedBy;
use Hyperf\Database\Model\Model;

#[ScopedBy([AncientScope::class, ActiveScope::class], priority: 10)]
#[ScopedBy(TenantScope::class, priority: 100)]
class User extends Model
{
}
```

## 註冊行為

觸發 `BootApplication` 時，組件會讀取所有帶有 `#[ScopedBy]` 註解的模型類別。
對於每個聲明的 Scope 類別，組件會：

1. 驗證類別存在並實現 `Hyperf\Database\Model\Scope`；
2. 檢查容器中是否存在該類別；
3. 從容器解析實例，並將其傳給 `Model::addGlobalScope()`。

未通過任一檢查的 Scope 項目會被略過。Scope 建構函式可以使用容器能夠解析的
依賴。相同優先級的 Scope 之間不保證註冊順序。
