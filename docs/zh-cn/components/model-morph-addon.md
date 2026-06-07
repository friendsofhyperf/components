# Model Morph Addon

Model Morph Addon 允许声明 `morphTo` 关联的模型自行解析多态别名，而不必只依赖全局多态映射。

## 要求

- Hyperf 3.2
- `hyperf/database` ~3.2
- `hyperf/di` ~3.2

## 安装

```shell
composer require friendsofhyperf/model-morph-addon
```

包的 `ConfigProvider` 会自动注册两个 AOP 切面。它没有需要发布的配置文件，也没有可选的集成依赖。

## 定义模型局部多态映射

在声明 `morphTo()` 的模型上重写 `getActualClassNameForMorph()`。每个关联模型应通过
`getMorphClass()` 返回数据库中存储的别名。

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

解析器应为每个输入返回有效的模型类。未知别名返回原值，可以保留 Hyperf 的默认回退行为。

## 查询所有多态类型

当 `hasMorph()` 接收到通配符类型列表 `['*']` 时，组件也会应用模型局部解析器。委托给
`hasMorph()` 的方法（例如 `whereHasMorph()` 和 `doesntHaveMorph()`）具有相同行为。

```php
$images = Image::query()
    ->whereHasMorph('imageable', ['*'])
    ->get();
```

对于 `['*']`，Hyperf 会发现模型表中存储的不同且非空的多态类型值。组件会先通过
`Image::getActualClassNameForMorph()` 解析每个值，再由 Hyperf 构建关联查询。

## 行为

- 加载 `Image::imageable` 关联时，会通过 `Image::getActualClassNameForMorph()` 解析其存储类型。
- 多态别名属于声明 `morphTo()` 的模型；不同模型可以用不同方式解析同一别名。
- 传给多态查询方法的显式类型列表保持 Hyperf 的默认行为。查询切面只改变完全匹配的通配符列表
  `['*']`。
