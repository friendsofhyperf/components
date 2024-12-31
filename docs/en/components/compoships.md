# Compoships

**Compoships** provides the ability to specify relationships based on two (or more) columns in Hyperf's Model ORM. This is particularly useful when dealing with third-party or pre-existing schemas/databases where it's common to have the need to match multiple columns in the definition of Eloquent relationships.

## The Problem

Eloquent does not support composite keys. As a result, there is no way to define a relationship from one model to another by matching multiple columns. Attempting to use a `where` clause (as shown in the example below) does not work when eager loading relationships because **$this->team_id** is null when the relationship is being processed.

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

It is recommended to install **Compoships** via [Composer](http://getcomposer.org/).

```shell
composer require friendsofhyperf/compoships
```

## Usage

### Using the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` Class

Simply have your model class extend the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` base class. `FriendsOfHyperf\Compoships\Database\Eloquent\Model` extends the `Eloquent` base class without altering its core functionality.

### Using the `FriendsOfHyperf\Compoships\Compoships` Trait

If for some reason you cannot extend your model from `FriendsOfHyperf\Compoships\Database\Eloquent\Model`, you can take advantage of the `FriendsOfHyperf\Compoships\Compoships` trait. Just use the trait in your model.

**Note:** To define a multi-column relationship from model *A* to another model *B*, **both models must extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` or use the `FriendsOfHyperf\Compoships\Compoships` trait**

### Usage

... Now we can define a relationship from model *A* to another model *B* by matching two or more columns (by passing an array of columns instead of a string).

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

As an example, let's assume we have a task list with categories, managed by multiple user teams, where:

- A task belongs to a category
- A task is assigned to a team
- A team has many users
- A user belongs to a team
- A user is responsible for tasks in a category

The user responsible for a specific task is the user currently responsible for that category within the assigned team.

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