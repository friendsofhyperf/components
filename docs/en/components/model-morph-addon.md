# Model Morph Addon

Model Morph Addon lets the model that declares a `morphTo` relationship resolve
its own morph aliases instead of relying only on the global morph map.

## Requirements

- Hyperf 3.2
- `hyperf/database` ~3.2
- `hyperf/di` ~3.2

## Installation

```shell
composer require friendsofhyperf/model-morph-addon
```

The package's `ConfigProvider` automatically registers its two AOP aspects. It
has no configuration file to publish and no optional integration dependencies.

## Define a Model-Local Morph Map

Override `getActualClassNameForMorph()` on the model that declares `morphTo()`.
Each related model should return the stored alias from `getMorphClass()`.

```php
namespace App\Model;

use Hyperf\Database\Model\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }

    public static function getActualClassNameForMorph($class)
    {
        $morphMap = [
            'user' => User::class,
            'book' => Book::class,
        ];

        return $morphMap[$class] ?? $class;
    }
}

class Book extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getMorphClass()
    {
        return 'book';
    }
}

class User extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getMorphClass()
    {
        return 'user';
    }
}
```

The resolver should return a valid model class for every input. Returning the
original value for unknown aliases preserves Hyperf's default fallback.

## Query All Morph Types

The component also applies the model-local resolver when `hasMorph()` receives
the wildcard type list `['*']`. Methods that delegate to `hasMorph()`, such as
`whereHasMorph()` and `doesntHaveMorph()`, receive the same behavior.

```php
$images = Image::query()
    ->whereHasMorph('imageable', ['*'])
    ->get();
```

For `['*']`, Hyperf discovers the distinct, non-empty morph type values stored
in the model's table. The component resolves each value through
`Image::getActualClassNameForMorph()` before Hyperf builds the relationship
queries.

## Behavior

- Loading an `Image::imageable` relation resolves its stored type through
  `Image::getActualClassNameForMorph()`.
- Morph aliases are local to the model that declares `morphTo()`; different
  models can resolve the same alias differently.
- Explicit type lists passed to morph query methods keep Hyperf's default
  behavior. The query aspect changes only the exact wildcard list `['*']`.
