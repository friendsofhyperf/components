# ReCaptcha

適用於 Hyperf 的 Google ReCaptcha 元件。

## 安裝

```shell
composer require friendsofhyperf/recaptcha
```

## 設定

使用中介軟體或驗證規則前，先發佈 `config/autoload/recaptcha.php`：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/recaptcha
```

透過 `RECAPTCHA_SECRET_V2_KEY` 設定 reCAPTCHA v2 金鑰，透過 `RECAPTCHA_SECRET_V3_KEY`
設定 reCAPTCHA v3 金鑰，也可以直接修改發佈檔案中的 `v2.secret_key` 與 `v3.secret_key`。
`default` 用於選擇未明確傳入版本時使用的版本。

## 使用

- 定義中介軟體

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

- 驗證器使用

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
