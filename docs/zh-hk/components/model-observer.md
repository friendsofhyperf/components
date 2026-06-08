# Model Observer

Model Observer 組件用於註冊處理 Hyperf 模型事件的類別。觀察者可以在觀察者類別上綁定，也可以在模型類別上綁定。

## 安裝

```shell
composer require friendsofhyperf/model-observer
```

組件會透過 ConfigProvider 自動註冊。與 Hyperf 模型一起使用時需要安裝 `hyperf/database`：

```shell
composer require hyperf/database
```

## 生成觀察者

向 `gen:observer` 傳入模型類別，可在預設的 `App\Observer` 命名空間中生成觀察者：

```shell
php bin/hyperf.php gen:observer UserObserver --model="App\\Model\\User"
```

該命令還支援：

```shell
php bin/hyperf.php gen:observer UserObserver \
  --model="App\\Model\\User" \
  --namespace="App\\ModelObserver" \
  --force
```

- `--model`、`-M`：用於生成屬性和方法簽名的模型類別。
- `--namespace`、`-N`：生成的觀察者命名空間。
- `--force`、`-f`：覆蓋已存在的觀察者檔案。

可以在 `config/autoload/devtool.php` 中修改預設命名空間或範本：

```php
return [
    'generator' => [
        'observer' => [
            'namespace' => 'App\\ModelObserver',
            'stub' => BASE_PATH . '/stubs/observer.stub',
        ],
    ],
];
```

## 從觀察者綁定

在觀察者類別上使用 `#[Observer]`。`model` 參數接受一個模型類別或模型類別陣列：

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;

#[Observer(model: User::class)]
class UserObserver
{
    public function creating(User $model): void
    {
        // 建立用戶前執行。
    }

    public function created(User $model): void
    {
        // 建立用戶後執行。
    }
}
```

```php
namespace App\Observer;

use App\Model\Post;
use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use Hyperf\Database\Model\Model;

#[Observer(model: [User::class, Post::class])]
class SearchIndexObserver
{
    public function saved(Model $model): void
    {
        // 更新搜尋索引。
    }
}
```

`#[Observer]` 可以重複聲明。使用 `priority` 可讓優先級較高的觀察者先執行：

```php
#[Observer(model: User::class, priority: 100)]
#[Observer(model: Post::class, priority: 50)]
class AuditObserver
{
    // ...
}
```

## 從模型綁定

使用 `#[ObservedBy]` 在模型上聲明觀察者。它的 `classes` 參數接受一個觀察者類別或觀察者類別陣列，
並且該屬性可以重複聲明：

```php
namespace App\Model;

use App\Observer\AuditObserver;
use App\Observer\UserObserver;
use FriendsOfHyperf\ModelObserver\Annotation\ObservedBy;
use Hyperf\Database\Model\Model;

#[ObservedBy([UserObserver::class, AuditObserver::class], priority: 100)]
class User extends Model
{
    // ...
}
```

來自 `#[Observer]` 和 `#[ObservedBy]` 的綁定會合併。同一模型的重複觀察者類別只會調用一次。

## 在協程中運行觀察者

如果觀察者上所有可調用的事件方法都應在新協程中運行，請實現 `ShouldCoroutine`：

```php
namespace App\Observer;

use App\Model\User;
use FriendsOfHyperf\ModelObserver\Annotation\Observer;
use FriendsOfHyperf\ModelObserver\Contract\ShouldCoroutine;

#[Observer(model: User::class)]
class UserObserver implements ShouldCoroutine
{
    public function created(User $model): void
    {
        // 在新建立的協程中運行。
    }
}
```

協程觀察者非同步運行，不要依賴它們在模型操作返回前執行完畢。

## 支援的事件

觀察者只需定義需要的方法。每個可調用方法都會接收模型實例：

- `booting`
- `booted`
- `retrieved`
- `creating`
- `created`
- `updating`
- `updated`
- `saving`
- `saved`
- `restoring`
- `restored`
- `deleting`
- `deleted`
- `forceDeleted`

組件透過 Hyperf 事件分發這些方法。觀察者方法的返回值會被忽略，不能用於取消模型操作。
