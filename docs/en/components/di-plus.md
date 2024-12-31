# DI Plus

Enhanced Dependency Injection Component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/di-plus
```

## Usage

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

Supports annotation-based injection

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