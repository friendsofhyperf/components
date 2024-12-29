# Compoships

**Compoships** provides the ability to define relationships based on two (or more) columns in Hyperf's Model ORM. When dealing with third-party or pre-existing schemas/databases, there is often a need to match multiple columns in the definition of Eloquent relationships.

## Problem

Eloquent does not support composite keys. Therefore, it is not possible to define relationships from one model to another by matching multiple columns. Attempting to use `where` clauses (as shown in the example below) does not work when eager loading relationships because **$this->team_id** is null when handling the relationship.

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

It is recommended to install the **Compoships** component using [Composer](http://getcomposer.org/).

```shell
composer require friendsofhyperf/compoships
```

## Usage

### Using the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` Class

Simply have your model classes extend the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` base class. `FriendsOfHyperf\Compoships\Database\Eloquent\Model` extends the `Eloquent` base class without altering its core functionality.

### Using the `FriendsOfHyperf\Compoships\Compoships` Trait

If for some reason you cannot extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` in your model, you can use the `FriendsOfHyperf\Compoships\Compoships` trait. Simply use this trait in your model.

**Note:** To define a multi-column relationship from model *A* to another model *B*, **both models must extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` or use the `FriendsOfHyperf\Compoships\Compoships` trait**

### Usage

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

As an example, suppose we have a task list with categories, managed by multiple user teams, where:

- A task belongs to a category
- A task is assigned to a team
- A team has many users
- A user belongs to a team
- A user is responsible for a category's tasks

The user responsible for a specific task is the currently responsible user for that category within the team.

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

The same syntax can be used to define the inverse relationship:

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