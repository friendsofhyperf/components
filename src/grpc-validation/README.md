# Hyperf grpc-validation

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/grpc-validation/version.png)](https://packagist.org/packages/friendsofhyperf/grpc-validation)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/grpc-validation/d/total.png)](https://packagist.org/packages/friendsofhyperf/grpc-validation)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/grpc-validation)](https://github.com/friendsofhyperf/grpc-validation)

The GRPC validation component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/grpc-validation
```

## Usage

```php
<?php

use FriendsOfHyperf\GrpcValidation\Annotation;

#[Validation(rules: [
    'name' => 'required|string|max:10',
    'message' => 'required|string|max:500',
])]
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
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
