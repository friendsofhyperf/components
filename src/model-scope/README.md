# model-scope

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/model-scope)](https://packagist.org/packages/friendsofhyperf/model-scope)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/model-scope)](https://packagist.org/packages/friendsofhyperf/model-scope)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/model-scope)](https://github.com/friendsofhyperf/model-scope)

The model scope annotation for Hyperf.

## Installation

```shell
composer require friendsofhyperf/model-scope
```

## Usage

- Custom scope

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

- Binding on model

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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
