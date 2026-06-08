# ReCaptcha

适用于 Hyperf 的 Google ReCaptcha 组件。

## 安装

```shell
composer require friendsofhyperf/recaptcha
```

## 配置

使用中间件或验证规则前，先发布 `config/autoload/recaptcha.php`：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/recaptcha
```

通过 `RECAPTCHA_SECRET_V2_KEY` 配置 reCAPTCHA v2 密钥，通过 `RECAPTCHA_SECRET_V3_KEY`
配置 reCAPTCHA v3 密钥，也可以直接修改发布文件中的 `v2.secret_key` 与 `v3.secret_key`。
`default` 用于选择未显式传入版本时使用的版本。

## 使用

- 定义中间件

```php
namespace App\Middleware;

use FriendsOfHyperf\ReCaptcha\Middleware\ReCaptchaMiddleware;

class V3CaptchaMiddleware extends ReCaptchaMiddleware
{
    protected string $version = 'v3';
    protected string $action = 'register'; 
    protected float $score = 0.35; 
    protected string $hostname; 
}

class V2CaptchaMiddleware extends ReCaptchaMiddleware
{
    protected string $version = 'v2';
    protected string $action = 'register'; 
    protected float $score = 0.35; 
    protected string $hostname; 
}
```

- 验证器使用

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function foo(RequestInterface $request)
    {
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'g-recaptcha' => 'required|recaptcha:register,0.34,hostname,v3',
            ],
            [
                'g-recaptcha.required' => 'g-recaptcha is required',
                'g-recaptcha.recaptcha' => 'Google ReCaptcha Verify Fails',
            ]
        );

        if ($validator->fails()){
            // Handle exception
            $errorMessage = $validator->errors()->first();  
        }
        // Do something
    }
}
```
