# Compoships

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/compoships/v/stable.svg)](https://packagist.org/packages/friendsofhyperf/compoships)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/compoships)](https://packagist.org/packages/friendsofhyperf/compoships)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/compoships)](https://github.com/friendsofhyperf/compoships)

**Compoships** offers the ability to specify relationships based on two (or more) columns in Hyperf's Model ORM. The need to match multiple columns in the definition of an Eloquent relationship often arises when working with third party or pre existing schema/database.

## The problem

Eloquent doesn't support composite keys. As a consequence, there is no way to define a relationship from one model to another by matching more than one column. Trying to use `where clauses` (like in the example below) won't work when eager loading the relationship because at the time the relationship is processed **$this->team_id** is null.

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

The recommended way to install **Compoships** is through [Composer](http://getcomposer.org/)

```bash
composer require friendsofhyperf/compoships
```

## Usage

### Using the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` class

Simply make your model class derive from the `FriendsOfHyperf\Compoships\Database\Eloquent\Model` base class. The `FriendsOfHyperf\Compoships\Database\Eloquent\Model` extends the `Eloquent` base class without changing its core functionality.

### Using the `FriendsOfHyperf\Compoships\Compoships` trait

If for some reasons you can't derive your models from `FriendsOfHyperf\Compoships\Database\Eloquent\Model`, you may take advantage of the `FriendsOfHyperf\Compoships\Compoships` trait. Simply use the trait in your models.

**Note:** To define a multi-columns relationship from a model *A* to another model *B*, **both models must either extend `FriendsOfHyperf\Compoships\Database\Eloquent\Model` or use the `FriendsOfHyperf\Compoships\Compoships` trait**

### Syntax

... and now we can define a relationship from a model *A* to another model *B* by matching two or more columns (by passing an array of columns instead of a string).

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

As an example, let's pretend we have a task list with categories, managed by several teams of users where:

* a task belongs to a category
* a task is assigned to a team
* a team has many users
* a user belongs to one team
* a user is responsible for one category of tasks

The user responsible for a particular task is the user _currently_ in charge for the category inside the team.

```php
namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use \FriendsOfHyperf\Compoships\Compoships;
    
    public function tasks()
    {
        return $this->hasMany(Task::class, ['team_id', 'category_id'], ['team_id', 'category_id']);
    }
}
```

Again, same syntax to define the inverse of the relationship:

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

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
