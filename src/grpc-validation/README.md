# Hyperf grpc-validation

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/grpc-validation/version.png)](https://packagist.org/packages/friendsofhyperf/grpc-validation)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/grpc-validation/d/total.png)](https://packagist.org/packages/friendsofhyperf/grpc-validation)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/grpc-validation)](https://github.com/friendsofhyperf/grpc-validation)

## Installation

~~~base
composer require friendsofhyperf/grpc-validation
~~~

## Usage

~~~php
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
~~~
