# model-scope

The model scope annotation for Hyperf.

## 安装

```shell
composer require friendsofhyperf/model-scope
```

## 使用

- 定义 Scope

```php
namespace App\Model\Scope;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
 
class AncientScope implements Scope
{
    /**
     * Apply the scope to a given Model query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('created_at', '<', now()->subYears(2000));
    }
}
```

- 绑定到模型

```php
namespace App\Model;
 
use App\Model\Scope\AncientScope;
use FriendsOfHyperf\ModelScope\Annotation\ScopedBy;
 
#[ScopedBy(AncientScope::class)]
class User extends Model
{
    //
}
```
