# DI Plus

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/di-plus/version.png)](https://packagist.org/packages/friendsofhyperf/di-plus)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/di-plus/d/total.png)](https://packagist.org/packages/friendsofhyperf/di-plus)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/di-plus)](https://github.com/friendsofhyperf/di-plus)

The di plus component for Hyperf.

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
