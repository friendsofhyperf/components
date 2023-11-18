# Telescope

An elegant debug assistant for the hyperf framework.

## Functions

- [x] request
- [x] exception
- [x] sql
- [x] grpc client
- [x] redis
- [x] log
- [x] command
- [x] event
- [x] http client
- [x] cache

## Installation

```shell
composer require friendsofhyperf/telescope:~3.0.0
```

## Publish

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope
```

## Migrate

```shell
php bin/hyperf.php migrate
```

## Add Listener

```php
<?php

// config/autoload/listeners.php

return [
    FriendsOfHyperf\Telescope\Listener\RequestHandledListener::class,
];

```

## Add Middleware

```php
<?php

// config/autoload/middlewares.php

return [
    'grpc' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];

```

> TelescopeMiddleware or RequestHandledListener, you can choose one of them.

## Add env

```env
# telescope
TELESCOPE_DB_CONNECTION=default

TELESCOPE_ENABLE_REQUEST=true
TELESCOPE_ENABLE_COMMAND=true
TELESCOPE_ENABLE_GRPC=true
TELESCOPE_ENABLE_LOG=true
TELESCOPE_ENABLE_REDIS=true
TELESCOPE_ENABLE_EVENT=true
TELESCOPE_ENABLE_EXCEPTION=true
TELESCOPE_ENABLE_JOB=true
TELESCOPE_ENABLE_DB=true
TELESCOPE_ENABLE_GUZZLE=true
TELESCOPE_ENABLE_CACHE=true

TELESCOPE_SERVER_ENABLE=true
```

## Visit

`http://127.0.0.1:9509/telescope/requests`

<img src="./requests.jpg" />

<img src="./grpc.jpg" />

<img src="./exception.jpg" />
