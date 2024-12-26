# Model Morph Addon

模型多態擴展包，支持在 Hyperf 模型中使用多態關聯。

## 安裝

```shell
composer require friendsofhyperf/model-morph-addon
```

## 之前用法

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }
}

class Book extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

class User extends Model
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

// Global
Relation::morphMap([
    'user' => App\Model\User::class,
    'book' => App\Model\Book::class,
]);
```

## 現在用法

```php
<?php
namespace App\Model;

class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }

    // Privately-owned
    public static function getActualClassNameForMorph($class)
    {
        $morphMap = [
            'user' => User::class,
            'book' => Book::class,
        ];

        return Arr::get($morphMap, $class, $class);
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
