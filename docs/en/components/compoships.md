# Compoships

**Compoships** provides the ability to specify relationships based on two (or more) columns in Hyperf's Model ORM. The need to match multiple columns in Eloquent relationship definitions often arises when dealing with third-party or pre-existing schemas/databases.

## Problem

Eloquent doesn't support composite keys. Therefore, it's not possible to define a relationship from one model to another by matching multiple columns. Attempting to use a `where` clause (as shown in the example below) won't work with eager loading relationships because **$this->team_id** is null when processing the relationship.

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    public function tasks()
    {
        //WON'T WORK WITH EAGER LOADING!!!
        return $this->hasMany(Task::class)->where('team_id', $this->team_id);
    }
}
```

## Installation

It's recommended to install **Compoships** component through [Composer](http://getcomposer.org/).

```shell
composer require friendsofhyperf/compoships
```

## Usage

### Using `FriendsOfHyperf\Compoships\Database\Eloquent\Model` Class

Simply make your model class derive from the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` base class. `FriendsOfHyperf\Compoships\Database\Eloquent\Model` extends the `Eloquent` base class without changing its core functionality.

### Using `FriendsOfHyperf\Compoships\Compoships` Trait

If for some reason you can't derive your models from `FriendsOfHyperf\Compoships\Database\Eloquent\Model`, you can use the `FriendsOfHyperf\Compoships\Compoships` trait. Simply use this trait in your models.

**Note:** To define a multi-column relationship from model *A* to another model *B*, **both models must extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` or use the `FriendsOfHyperf\Compoships\Compoships` trait**

### Usage

... Now we can define relationships from model *A* to another model *B* by matching two or more columns (by passing arrays of columns instead of a string).

```php
namespace App;

use Hyperf\Database\Model\Model;

class A extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function b()
    {
        return $this->hasMany('B', ['foreignKey1', 'foreignKey2'], ['localKey1', 'localKey2']);
    }
}
```

We can use the same syntax to define the inverse of the relationship:

```php
namespace App;

use Hyperf\Database\Model\Model;

class B extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function a()
    {
        return $this->belongsTo('A', ['foreignKey1', 'foreignKey2'], ['ownerKey1', 'ownerKey2']);
    }
}
```

### Example

As an example, let's say we have a task list with categories managed by multiple user teams where:

- A task belongs to a category
- A task is assigned to a team
- A team has many users
- A user belongs to a team
- A user is responsible for tasks in a category

The user responsible for a specific task is the user currently responsible for that category within their team.

```php
namespace App;

use Hyperf\Database\Model\Model;

class User extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

The same syntax can be used to define the inverse of the relationship:

```php
namespace App;

use Hyperf\Database\Model\Model;

class Task extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function user()
    {
        return $this->belongsTo(User::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```
