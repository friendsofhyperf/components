# Model Scope

Model Scope 组件通过可重复使用的 `#[ScopedBy]` 注解注册 Hyperf 模型全局
Scope。

## 使用要求

- Hyperf 3.2
- `hyperf/database` ~3.2；使用模型 Scope 时必须安装，但本组件仅将其声明为
  Composer 建议依赖

## 安装

```shell
composer require friendsofhyperf/model-scope
```

组件的 `ConfigProvider` 会自动注册 Scope 监听器，无需发布配置文件。

## 定义 Scope

每个 Scope 都必须实现 `Hyperf\Database\Model\Scope`。

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

## 将 Scope 绑定到模型

在模型类上添加 `#[ScopedBy]`。应用启动时，该 Scope 会被注册为全局 Scope。

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

## 注册多个 Scope

`classes` 参数可以接收单个 Scope 类或 Scope 类数组，该注解也可以重复使用。
`priority` 默认为 `0`；优先级越高的 Scope 越先注册。同一个注解中传入的所有
类共用一个优先级。

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

## 注册行为

触发 `BootApplication` 时，组件会读取所有带有 `#[ScopedBy]` 注解的模型类。
对于每个声明的 Scope 类，组件会：

1. 验证类存在并实现 `Hyperf\Database\Model\Scope`；
2. 检查容器中是否存在该类；
3. 从容器解析实例，并将其传给 `Model::addGlobalScope()`。

未通过任一检查的 Scope 条目会被跳过。Scope 构造函数可以使用容器能够解析的
依赖。相同优先级的 Scope 之间不保证注册顺序。
