# Validated DTO

## Official Documentation

[Laravel Validated DTO Official Documentation](https://wendell-adriel.gitbook.io/laravel-validated-dto)

## Installation

```shell
composer require friendsofhyperf/validated-dto
```

## Creating a DTO

You can create a `DTO` using the `gen:dto` command:

```shell
php bin/hyperf.php gen:dto UserDTO
```

The `DTO` will be created in the `app/DTO` directory.

## Defining Validation Rules

You can validate data using the same rules as you would for validating `Request` data:

```php
<?php

namespace App\DTO;

class UserDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
```

## Creating a DTO Instance

You can create a `DTO` instance in various ways:

### From an Array

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);
```

### From a JSON String

```php
$dto = UserDTO::fromJson('{"name": "Deeka Wong", "email": "deeka@example.com", "password": "D3Crft!@1b2A"}');
```

### From a Request Object

```php
public function store(RequestInterface $request): JsonResponse
{
    $dto = UserDTO::fromRequest($request);
}
```

### From a Model

```php
$user = new User([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto = UserDTO::fromModel($user);
```

Note: Fields hidden via the `$hidden` property in the model will not be used in the `DTO`.

### From an Artisan Command

You can create a `DTO` instance from an `Artisan Command` in three ways:

#### From Command Arguments

```php
<?php

use App\DTO\UserDTO;
use Hyperf\Command\Command;

class CreateUserCommand extends Command
{
    protected ?string $signature = 'create:user {name} {email} {password}';

    protected string $description = 'Create a new User';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws ValidationException
     */
    public function handle()
    {
        $dto = UserDTO::fromCommandArguments($this);
    }
}
```

#### From Command Options

```php
<?php

use App\DTO\UserDTO;
use Hyperf\Command\Command;

class CreateUserCommand extends Command
{
    protected ?string $signature = 'create:user { --name= : The user name } { --email= : The user email } { --password= : The user password }';

    protected string $description = 'Create a new User';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws ValidationException
     */
    public function handle()
    {
        $dto = UserDTO::fromCommandOptions($this);
    }
}
```

#### From Both Command Arguments and Options

```php
<?php

use App\DTO\UserDTO;
use Hyperf\Command\Command;

class CreateUserCommand extends Command
{
    protected ?string $signature = 'create:user {name}
                                        { --email= : The user email }
                                        { --password= : The user password }';

    protected string $description = 'Create a new User';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws ValidationException
     */
    public function handle()
    {
        $dto = UserDTO::fromCommand($this);
    }
}
```

## Accessing DTO Data

Once you’ve created a `DTO` instance, you can access any property like an `object`:

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->name; // 'Deeka Wong'
$dto->email; // 'deeka@example.com'
$dto->password; // 'D3Crft!@1b2A'
```

If you pass attributes not defined in the `rules` method, those attributes will be ignored and unavailable in the `DTO`:

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
    'username' => 'john_doe',
]);

$dto->username; // This property is not available in the DTO
```

## Defining Default Values

Sometimes you may have optional properties with default values. You can define default values in the `defaults` method:

```php
<?php

namespace App\DTO;

use Hyperf\Stringable\Str;

class UserDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email'],
            'username' => ['sometimes', 'string'],
            'password' => ['required'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'username' => Str::snake($this->name),
        ];
    }
}
```

Using the above `DTO` definition, you can run:

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->username; // 'deeka_wong'
```

## Transforming DTO Data

You can transform the DTO into various formats:

### To an Array

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toArray();
// [
//     "name" => "Deeka Wong",
//     "email" => "deeka@example.com",
//     "password" => "D3Crft!@1b2A",
// ]
```

### To a JSON String

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toJson();
// '{"name":"Deeka Wong","email":"deeka@example.com","password":"D3Crft!@1b2A"}'

$dto->toJson(true); // Pretty-printed JSON
// {
//     "name": "Deeka Wong",
//     "email": "deeka@example.com",
//     "password": "D3Crft!@1b2A"
// }
```

### To an Eloquent Model

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toModel(\App\Model\User::class);
// App\Model\User {#3776
//     name: "Deeka Wong",
//     email: "deeka@example.com",
//     password: "D3Crft!@1b2A",
// }
```

## Custom Error Messages, Attributes, and Exceptions

You can define custom messages and attributes for the validator by implementing the `messages` and `attributes` methods in the `DTO` class:

```php
/**
 * Define custom error messages for the validator.
 */
protected function messages() {
    return [];
}

/**
 * Define custom attributes for the validator.
 */
protected function attributes() {
    return [];
}
```

## Type Casting

You can easily cast DTO properties by defining the `casts` method in the `DTO`:

```php
/**
 * Define type casting for DTO properties.
 *
 * @return array
 */
protected function casts(): array
{
    return [
        'name' => new StringCast(),
        'age'  => new IntegerCast(),
        'created_at' => new CarbonImmutableCast(),
    ];
}
```

## Available Cast Types

[Omitted for brevity but similar definitions apply.]  

## Creating Custom Cast Types

You can create custom cast types by implementing the `FriendsOfHyperf\ValidatedDTO\Casting\Castable` interface. This interface has one required method:

```php
/**
 * Cast the given value.
 *
 * @param  string  $property
 * @param  mixed  $value
 * @return mixed
 */
public function cast(string $property, mixed $value): mixed;
```

For example:

```php
class URLCast implements Castable
{
    public function cast(string $property, mixed $value): URLWrapper
    {
        return new URLWrapper($value);
    }
}
```

You can then apply this cast in your DTO:

```php
class CustomDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'url' => ['required', 'url'],
        ];
    }

    protected function casts(): array
    {
        return [
            'url' => new URLCast(),
        ];
    }
}
```