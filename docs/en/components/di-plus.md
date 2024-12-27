# DI Plus

The dependency injection enhancement component for Hyperf.

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

Support for annotation-based configuration:

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