# Model Scope

The Model Scope component registers Hyperf model global scopes through the
repeatable `#[ScopedBy]` attribute.

## Requirements

- Hyperf 3.2
- `hyperf/database` ~3.2, which is required when using model scopes but is only
  suggested by this package

## Installation

```shell
composer require friendsofhyperf/model-scope
```

The package's `ConfigProvider` automatically registers the scope listener. No
configuration file is required.

## Define a Scope

Each scope must implement `Hyperf\Database\Model\Scope`.

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

## Bind a Scope to a Model

Apply `#[ScopedBy]` to the model class. The scope is registered as a global
scope when the application boots.

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

## Register Multiple Scopes

The `classes` argument accepts either one scope class or an array of scope
classes. The attribute is also repeatable. `priority` defaults to `0`; scopes
with a higher priority are registered first. One priority applies to every
class passed in the same attribute.

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

## Registration Behavior

On `BootApplication`, the component reads all model classes annotated with
`#[ScopedBy]`. For every declared scope class, it:

1. verifies that the class exists and implements
   `Hyperf\Database\Model\Scope`;
2. checks that the class is available from the container;
3. resolves it from the container and passes the instance to
   `Model::addGlobalScope()`.

Scope entries that fail one of these checks are skipped. Scope constructors may
use dependencies that the container can resolve. Ordering between scopes with
the same priority is not guaranteed.
