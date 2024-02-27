# Http logger

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/http-logger)](https://packagist.org/packages/friendsofhyperf/http-logger)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/http-logger)](https://packagist.org/packages/friendsofhyperf/http-logger)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/http-logger)](https://github.com/friendsofhyperf/http-logger)

The http logger component for Hyperf.

## Installation

- Request

```shell
composer require "friendsofhyperf/http-logger
```

- Publish

```shell
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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
