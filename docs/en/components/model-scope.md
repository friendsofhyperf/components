# Model Scope

The model scope annotation for Hyperf.

## Installation

```shell
composer require friendsofhyperf/model-scope
```

## Usage

- Define Scope

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

- Bind to Model

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
