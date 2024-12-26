# Grpc Validation

適用於 Hyperf 的 GRPC 驗證元件。

## 安裝

```shell
composer require friendsofhyperf/grpc-validation
```

## 使用

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
