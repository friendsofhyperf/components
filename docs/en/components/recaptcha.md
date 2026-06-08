# ReCaptcha

A Google ReCaptcha component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/recaptcha
```

## Configuration

Publish `config/autoload/recaptcha.php` before using the middleware or validator:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/recaptcha
```

Set `RECAPTCHA_SECRET_V2_KEY` for reCAPTCHA v2 and `RECAPTCHA_SECRET_V3_KEY` for reCAPTCHA v3,
or configure `v2.secret_key` and `v3.secret_key` in the published file. The `default` key selects
the version used when no version is passed explicitly.

## Usage

- Define Middleware

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

- Validator Usage

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