# Model Scope

Model Scope 元件透過可重複使用的 `#[ScopedBy]` 註解註冊 Hyperf 模型全域性
Scope。

## 使用要求

- Hyperf 3.2
- `hyperf/database` ~3.2；使用模型 Scope 時必須安裝，但本元件僅將其宣告為
  Composer 建議依賴

## 安裝

```shell
composer require friendsofhyperf/model-scope
```

元件的 `ConfigProvider` 會自動註冊 Scope 監聽器，無需釋出配置檔案。

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

## 將 Scope 繫結到模型

在模型類上新增 `#[ScopedBy]`。應用啟動時，該 Scope 會被註冊為全域性 Scope。

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

`classes` 引數可以接收單個 Scope 類或 Scope 類陣列，該註解也可以重複使用。
`priority` 預設為 `0`；優先順序越高的 Scope 越先註冊。同一個註解中傳入的所有
類共用一個優先順序。

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

觸發 `BootApplication` 時，元件會讀取所有帶有 `#[ScopedBy]` 註解的模型類。
對於每個宣告的 Scope 類，元件會：

1. 驗證類存在並實現 `Hyperf\Database\Model\Scope`；
2. 檢查容器中是否存在該類；
3. 從容器解析例項，並將其傳給 `Model::addGlobalScope()`。

未透過任一檢查的 Scope 條目會被跳過。Scope 建構函式可以使用容器能夠解析的
依賴。相同優先順序的 Scope 之間不保證註冊順序。
