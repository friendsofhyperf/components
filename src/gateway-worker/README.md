# Gateway Worker For Hyperf

[![Latest Test](https://github.com/friendsofhyperf/gateway-worker/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/gateway-worker/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/gateway-worker.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/gateway-worker)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/gateway-worker.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/gateway-worker)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/gateway-worker)](https://github.com/friendsofhyperf/gateway-worker)

The gateway-worker component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/gateway-worker
```

publish

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/gateway-worker
```

## Usage

```shell
php bin/hyperf.php gateway-worker:serve [start|stop|restart|status|connections|help]
```

for help

```shell
php bin/hyperf.php gateway-worker:serve --help
```

## Cluster

- Cluster

|Role|IP|Command|
|--|--|--|
|Register|192.168.1.101|`php bin/hyperf.php gateway-worker:serve --register --register-bind=0.0.0.0:1215`|
|Gateway|192.168.2.102-105|`php bin/hyperf.php gateway-worker:serve --gateway --gateway-bind=0.0.0.0:1216 --register-address=192.168.1.101:1215 --lan-ip=192.168.1.xxx`|
|Businessworker|192.168.1.106-110|`php bin/hyperf.php gateway-worker:serve --businessworker --register-address=192.168.1.101:1215`|

- In One

```shell
php bin/hyperf.php gateway-worker:serve --register --gateway --businessworker
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
