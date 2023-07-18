# Http logger

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/http-logger)](https://packagist.org/packages/friendsofhyperf/http-logger)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/http-logger)](https://packagist.org/packages/friendsofhyperf/http-logger)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/http-logger)](https://github.com/friendsofhyperf/http-logger)

The http logger component for Hyperf.

## Installation

- Request

```bash
composer require "friendsofhyperf/http-logger
```

- Publish

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/http-logger
```

## Usage

```php
return [
    'http' => [
        \FriendsOfHyperf\Http\Logger\Middleware\HttpLogger::class,
    ],
];
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
