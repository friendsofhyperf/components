# Async Task

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/async-task)](https://packagist.org/packages/friendsofhyperf/async-task)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/async-task)](https://packagist.org/packages/friendsofhyperf/async-task)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/async-task)](https://github.com/friendsofhyperf/async-task)

The async task component for Hyperf.

## Installation

```bash
composer require friendsofhyperf/async-task
```

## Usage

```php
use FriendsOfHyperf\AsyncTask\AbstractTask;
use FriendsOfHyperf\AsyncTask\Task;

class FooTask extends AbstractTask
{
    public function handle():void
    {
        var_dump('foo');
    }
}

Task::deliver(new FooTask());

Task::deliver(fn () => var_dump(111));
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
