# Compoships

**Compoships** lets Hyperf's Model ORM define relationships that match two or more
columns. It is intended for third-party or legacy schemas where a single foreign key
column is not enough to identify the related records.

Compoships only extends relationship handling. It does not make Hyperf's model primary
key itself a composite primary key.

## The Problem

Eloquent-style relationships normally match one foreign key column to one local or
owner key column. Adding a `where` clause to the relationship is not a replacement,
because eager loading builds the relationship query before an individual model instance
is available. In the example below, `$this->team_id` is `null` while the eager-loading
constraints are being prepared.

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    public function tasks()
    {
        return $this->hasMany(Task::class)->where('team_id', $this->team_id);
    }
}
```

## Installation

Install the component with Composer:

```shell
composer require friendsofhyperf/compoships
```

This component targets Hyperf 3.2 and requires `hyperf/database` and the supporting
Hyperf packages declared by the component. The package suggestions in `composer.json`
are optional third-party packages and are not required for Compoships relationships.

## Configuration

No publishable configuration is required. The component provides a Hyperf
`ConfigProvider`, but it currently returns an empty configuration array.

## Usage

### Use the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` Class

Extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` instead of
`Hyperf\Database\Model\Model`. This base model uses the Compoships trait and keeps the
normal Hyperf model behavior.

```php
namespace App;

use FriendsOfHyperf\Compoships\Database\Eloquent\Model;

class User extends Model
{
}
```

### Use the `FriendsOfHyperf\Compoships\Compoships` Trait

If your model must extend another base class, use the
`FriendsOfHyperf\Compoships\Compoships` trait on the model.

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class User extends Model
{
    use Compoships;
}
```

When a relationship uses an array of keys, the related model must also use Compoships,
either by extending `FriendsOfHyperf\Compoships\Database\Eloquent\Model` or by using the
`FriendsOfHyperf\Compoships\Compoships` trait. Otherwise the relationship definition
throws `FriendsOfHyperf\Compoships\Exceptions\InvalidUsageException`.

## Relationship Syntax

Compoships supports composite keys on these relationship methods:

- `hasOne($related, $foreignKey = null, $localKey = null)`
- `hasMany($related, $foreignKey = null, $localKey = null)`
- `belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)`

Pass arrays instead of strings for the key arguments. Keep the arrays in the same order
and with the same number of items, because values are matched by array index.

For `hasOne` and `hasMany`, the foreign-key array names columns on the related model,
and the local-key array names columns on the current model:

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Team extends Model
{
    use Compoships;

    public function latestTask()
    {
        return $this->hasOne(Task::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }
}
```

For `belongsTo`, the foreign-key array names columns on the current model, and the
owner-key array names columns on the related model:

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Task extends Model
{
    use Compoships;

    public function team()
    {
        return $this->belongsTo(Team::class, ['team_id', 'category_id'], ['id', 'category_id']);
    }
}
```

## Example

Assume a task list is managed by multiple teams, and each team has one user responsible
for each task category:

- A task belongs to a category.
- A task is assigned to a team.
- A team has many users.
- A user belongs to a team.
- A user is responsible for tasks in one category.

The user responsible for a task is the user responsible for that task's category inside
the assigned team.

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class User extends Model
{
    use Compoships;

    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

The inverse relationship uses the same pair of columns:

```php
namespace App;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model;

class Task extends Model
{
    use Compoships;

    public function user()
    {
        return $this->belongsTo(
            User::class,
            ['team_id', 'category_id'],
            ['team_id', 'category_id']
        );
    }
}
```

## Behavior Notes

Compoships uses a custom query builder so eager loading can apply multi-column
`whereIn` constraints and relationship existence queries can compare multiple columns
with `whereColumn`.

For `hasOne` and `hasMany`, `save()` and `create()` fill each related foreign-key column
from the matching parent local-key value. For `belongsTo`, `associate()` fills each
child foreign-key column from the matching owner-key value.
