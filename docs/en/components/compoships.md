# Compoships

**Compoships** provides the ability to specify relationships in Hyperf's Model ORM based on two (or more) columns. This is commonly needed when working with third-party or pre-existing schemas/databases where relationships in Eloquent are defined by matching multiple columns.

## Issue

Eloquent does not support composite keys. As a result, it is not possible to define relationships from one model to another by matching multiple columns. Attempting to use a `where` clause (as shown below) will not work when eager-loading the relationship, because **$this->team_id** is null during relationship processing.

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

It is recommended to install the **Compoships** package via [Composer](http://getcomposer.org/).

```shell
composer require friendsofhyperf/compoships
```

## Usage

### Using the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` Class

Simply extend your model class from the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` base class. `FriendsOfHyperf\Compoships\Database\Eloquent\Model` extends the `Eloquent` base class without altering its core functionality.

### Using the `FriendsOfHyperf\Compoships\Compoships` Trait

If, for some reason, you cannot extend your model from `FriendsOfHyperf\Compoships\Database\Eloquent\Model`, you can use the `FriendsOfHyperf\Compoships\Compoships` trait. Simply include the trait in your model.

**Note:** To define multi-column relationships from model *A* to another model *B*, **both models must either extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` or use the `FriendsOfHyperf\Compoships\Compoships` trait**.

### How It Works

... Now we can define relationships from model *A* to another model *B* by matching two or more columns (by passing an array of columns instead of a string).

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

We can use the same syntax to define the inverse relationship:

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

For example, suppose we have a task list with categories that are managed by multiple user teams, where:

- A task belongs to a category
- A task is assigned to a team
- A team has many users
- A user belongs to a team
- A user is responsible for tasks in a category

The user responsible for a specific task is the current user in charge of that category within the team.

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

The same syntax applies to defining the inverse relationship:

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
