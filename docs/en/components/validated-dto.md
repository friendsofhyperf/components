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

You can validate data just like you would with `Request` data:

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

You can create a `DTO` instance in several ways:

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

Note that fields in the model's `$hidden` property will not be used for the `DTO`.

### From an Artisan Command

You have three ways to create a `DTO` instance from an `Artisan Command`:

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

#### From Command Arguments and Options

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

After creating a `DTO` instance, you can access any property as you would with an `object`:

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

If you pass properties that are not in the `rules` method of the `DTO`, those data will be ignored and will not be available in the `DTO`:

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

Sometimes we may have optional properties that can have default values. You can define default values for `DTO` properties in the `defaults` method:

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

With the above `DTO` definition, you can run:

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->username; // 'deeka_wong'
```

## Converting DTO Data

You can convert your DTO to some formats:

### To Array

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

### To JSON String

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toJson();
// '{"name":"Deeka Wong","email":"deeka@example.com","password":"D3Crft!@1b2A"}'

$dto->toJson(true); // You can call it like this to pretty-print your JSON
// {
//     "name": "Deeka Wong",
//     "email": "deeka@example.com",
//     "password": "D3Crft!@1b2A"
// }
```

### To Eloquent Model

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

You can define custom messages and attributes by implementing the `messages` and `attributes` methods in the `DTO` class:

```php
/**
 * Define custom messages for validator errors.
 */
protected function messages() {
    return [];
}

/**
 * Define custom attributes for validator errors.
 */
protected function attributes() {
    return [];
}
```

## Type Casting

You can easily cast your DTO properties by defining the `casts` method in your DTO:

```php
/**
 * Define the type casting for DTO properties.
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

## Available Casts

### Array

For JSON strings, it will be converted to an array, and for other types, it will be wrapped in an array.

```php
protected function casts(): array
{
    return [
        'property' => new ArrayCast(),
    ];
}
```

### Boolean

For string values, this uses the `filter_var` function with the `FILTER_VALIDATE_BOOLEAN` flag.

```php
protected function casts(): array
{
    return [
        'property' => new BooleanCast(),
    ];
}
```

### Carbon

This accepts any value that the `Carbon` constructor accepts. If an invalid value is found, it will throw a `\FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast(),
    ];
}
```

You can also pass a timezone when defining the cast, which will be used when casting the value if needed.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon'),
    ];
}
```

You can also pass a format when defining the cast to be used when casting the value. If the property's format is different from the specified one, it will throw a `\FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### CarbonImmutable

This accepts any value that the `CarbonImmutable` constructor accepts. If an invalid value is found, it will throw a `\FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast(),
    ];
}
```

You can also pass a timezone when defining the cast, which will be used when casting the value if needed.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon'),
    ];
}
```

You can also pass a format when defining the cast to be used when casting the value. If the property's format is different from the specified one, it will throw a `\FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### Collection

For JSON strings, it will first be converted to an array and then wrapped in a `Collection` object.

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(),
    ];
}
```

If you want to cast all elements in the `Collection`, you can pass a `Castable` to the `CollectionCast` constructor. Suppose you want to cast all items in the `Collection` to integers:

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(new IntegerCast()),
    ];
}
```

This works for all `Castable`, including `DTOCast` and `ModelCast` for nested data.

### DTO

This works for arrays and JSON strings. This will validate the data and cast it for the given DTO.

If the data is invalid for the DTO, this will throw a `Hyperf\Validation\ValidationException`.

If the property is not a valid array or a valid JSON string, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

If the class passed to the `DTOCast` constructor is not an instance of `ValidatedDTO`, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException`.

```php
protected function casts(): array
{
    return [
        'property' => new DTOCast(UserDTO::class),
    ];
}
```

### Float

If a non-numeric value is found, it will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new FloatCast(),
    ];
}
```

### Integer

If a non-numeric value is found, it will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new IntegerCast(),
    ];
}
```

### Model

This works for arrays and JSON strings.

If the property is not a valid array or a valid JSON string, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

If the class passed to the `ModelCast` constructor is not an instance of `Model`, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException`.

```php
protected function casts(): array
{
    return [
        'property' => new ModelCast(User::class),
    ];
}
```

### Object

This works for arrays and JSON strings.

If the property is not a valid array or a valid JSON string, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new ObjectCast(),
    ];
}
```

### String

If the data cannot be cast to a string, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`.

```php
protected function casts(): array
{
    return [
        'property' => new StringCast(),
    ];
}
```

## Creating Your Own Casts

You can easily create new `Castable` types for your project by implementing the `FriendsOfHyperf\ValidatedDTO\Casting\Castable` interface. This interface has one method that must be implemented:

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

Suppose you have a `URLWrapper` class in your project, and you want it to always return a `URLWrapper` instance instead of a simple string when a URL is passed to your `DTO`:

```php
class URLCast implements Castable
{
    /**
     * @param  string  $property
     * @param  mixed  $value
     * @return URLWrapper
     */
    public function cast(string $property, mixed $value): URLWrapper
    {
        return new URLWrapper($value);
    }
}
```

Then you can apply it to your DTO:

```php
class CustomDTO extends ValidatedDTO
{
    protected function rules(): array
    {
        return [
            'url' => ['required', 'url'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'url' => new URLCast(),
        ];
    }
}
```