# ReCaptcha

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/recaptcha)](https://packagist.org/packages/friendsofhyperf/recaptcha)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/recaptcha)](https://packagist.org/packages/friendsofhyperf/recaptcha)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/recaptcha)](https://github.com/friendsofhyperf/recaptcha)

The Google recaptcha component for Hyperf.

## Installation

- Request

```bash
composer require friendsofhyperf/recaptcha
```

## Usage

- Middleware

```php
namespace App\Middleware;

use FriendsOfHyperf\ReCaptcha\Middleware\ReCaptchaMiddleware;

class V3CaptchaMiddleware extends ReCaptchaMiddleware
{
    protected $version = 'v3';
    protected $action = 'register'; 
    protected $score = 0.35; 
    protected $hostname; 
}

class V2CaptchaMiddleware extends ReCaptchaMiddleware
{
    protected $version = 'v2';
    protected $action = 'register'; 
    protected $score = 0.35; 
    protected $hostname; 
}
```

- Validator

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class IndexController
{
    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

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

## Sponsor

If you like this project, Buy me a cup of coffee. [ [Alipay](https://hdj.me/images/alipay.jpg) | [WePay](https://hdj.me/images/wechat-pay.jpg) ]
