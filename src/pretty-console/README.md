# Pretty Console

[![Latest Test](https://github.com/friendsofhyperf/pretty-console/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/pretty-console/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/pretty-console)](https://packagist.org/packages/friendsofhyperf/pretty-console)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/pretty-console)](https://packagist.org/packages/friendsofhyperf/pretty-console)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/pretty-console)](https://github.com/friendsofhyperf/pretty-console)

The pretty console component for Hyperf.

[![image](https://user-images.githubusercontent.com/5457236/178333036-b11abb56-ba70-4c0d-a2f6-79afe3a0a78c.png)](#)

## Installation

```shell
composer require friendsofhyperf/pretty-console
```

## Usage

```php
<?php
use FriendsOfHyperf\PrettyConsole\Traits\Prettyable;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    use Prettyable;

    public function function handle()
    {
        $this->components->info('Your message here.');
    }
}
```

## Thanks

- [nunomaduro/termwind](https://github.com/nunomaduro/termwind)
- [The idea from pr of laravel](https://github.com/laravel/framework/pull/43065)

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
