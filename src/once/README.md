# Once

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/once/version.png)](https://packagist.org/packages/friendsofhyperf/once)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/once/d/total.png)](https://packagist.org/packages/friendsofhyperf/once)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/once)](https://github.com/friendsofhyperf/once)

A magic memoization function for Hyperf.

## Installation

- Installation

```bash
composer require friendsofhyperf/once
```

## Documentation

- [Documentation](https://github.com/spatie/once)

## Usage

```php
use FriendsOfHyperf\Once\Annotation\Forget;
use FriendsOfHyperf\Once\Annotation\Once;

class Foo
{
    #[Once]
    public function getNumber(): int
    {
        return rand(1, 10000);
    }

    #[Forget]
    public function forgetNumber()
    {
    }
}
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
