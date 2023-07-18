# mysql-grammar-addon

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/mysql-grammar-addon/v/stable.svg)](https://packagist.org/packages/friendsofhyperf/mysql-grammar-addon)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/mysql-grammar-addon)](https://packagist.org/packages/friendsofhyperf/mysql-grammar-addon)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/mysql-grammar-addon)](https://github.com/friendsofhyperf/mysql-grammar-addon)

The MySqlGrammar addon for Hyperf.

## Installation

```shell
composer require friendsofhyperf/mysql-grammar-addon --dev
```

## Before

```php
/**
 * @property int $id
 * @property int $user_id ??id
 * @property string $group_name ????
 * @property string $event_name ????
 * @property string $page_name ??
 * @property string $extra ????
 * @property string $device pc,android,ios,touch
 * @property string $device_id ???
 * @property \Carbon\Carbon $created_at ????
 */
class Event extends Model
{}
```

## After

```php
/**
 * @property int $id 
 * @property int $user_id 用户id
 * @property string $group_name 事件分组
 * @property string $event_name 事件名称
 * @property string $page_name 页面
 * @property string $extra 额外信息
 * @property string $device pc,android,ios,touch
 * @property string $device_id 设备号
 * @property \Carbon\Carbon $created_at 创建时间
 */
class Event extends Model
{}
```

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat | Buy Me A Coffee |
|  ----  |  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> | <img src="https://hdj.me/images/bmc_qr.png" width="200" height="200" /> |

<a href="https://www.buymeacoffee.com/huangdijiag" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
