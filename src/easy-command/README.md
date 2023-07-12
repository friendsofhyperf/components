# easy-command

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/easy-command)](https://packagist.org/packages/friendsofhyperf/easy-command)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/easy-command)](https://packagist.org/packages/friendsofhyperf/easy-command)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/easy-command)](https://github.com/friendsofhyperf/easy-command)

The easy command component for Hyperf.

## Installation

```bash
composer require friendsofhyperf/easy-command
```

## Usage

- Define Command using Annotation

```php
<?php

namespace App\Service;

use FriendsOfHyperf\EasyCommand\Annotation\Command;
use FriendsOfHyperf\EasyCommand\Concerns\InteractsWithIO;

#[Command(signature: 'foo:bar1', handle: 'bar1', description: 'The description of foo:bar1 command.')]
#[Command(signature: 'foo', description: 'The description of foo command.')]
class FooService
{
    use InteractsWithIO;

    #[Command(signature: 'foo:bar {--bar=1 : Bar Value}', description: 'The description of foo:bar command.')]
    public function bar($bar)
    {
        $this->output?->info('Bar Value: ' . $bar);

        return $bar;
    }

    public function bar1()
    {
        $this->output?->info(__METHOD__);
    }

    public function handle()
    {
        $this->output?->info(__METHOD__);
    }
}
```

Run `php bin/hyperf.php`

```shell
foo
  foo:bar                   The description of foo:bar command.
  foo:bar1                  The description of foo:bar1 command.
```

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  | ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
