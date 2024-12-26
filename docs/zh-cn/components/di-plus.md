# DI Plus

Hyperf 的依赖注入增强组件。

## 安装

```shell
composer require friendsofhyperf/di-plus
```

## 使用

```php
<?php
// config/autoload/dependencies.php
return [
    'App\Bar@App\Foo1' => App\BarAtFoo1Factory::class,
    'App\Bar@App\Foo2' => App\BarAtFoo2Factory::class,
];
```

```php
<?php
namespace App;

class Foo1
{
    public function __construct(public Bar $bar)
    {
    }
}

class Foo2
{
    public function __construct(public Bar $bar)
    {
    }
}
```

支持注解的方式

```php
<?php
namespace App;

use Hyperf\Di\Annotation\Inject;

class Foo1
{
    #[Inject]
    public Bar $bar;
}

class Foo2
{
    #[Inject]
    public Bar $bar;
}
```
