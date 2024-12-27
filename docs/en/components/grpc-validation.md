# Grpc Validation

GRPC validation component for Hyperf.

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