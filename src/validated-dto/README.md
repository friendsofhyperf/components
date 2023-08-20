# Validated DTO

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/validated-dto)](https://packagist.org/packages/friendsofhyperf/validated-dto)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/validated-dto)](https://packagist.org/packages/friendsofhyperf/validated-dto)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/validated-dto)](https://github.com/friendsofhyperf/validated-dto)

The Data Transfer Objects with validation for Hyperf applications. Forked from [laravel-validated-dto](https://github.com/WendellAdriel/laravel-validated-dto)

## Installation

```shell
composer require friendsofhyperf/validated-dto
```

## Generating DTO

You can create `DTO` using the `gen:dto` command:

```shell
php bin/hyperf.php gen:dto UserDTO
```

The `DTO` are going to be created inside `app/DTO`.

## Defining Validation Rules

You can validate data in the same way you validate `Request` data:

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

## Creating DTO instances

You can create a `DTO` instance on many ways:

### From arrays

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);
```

### From JSON strings

```php
$dto = UserDTO::fromJson('{"name": "Deeka Wong", "email": "deeka@example.com", "password": "D3Crft!@1b2A"}');
```

### From Request objects

```php
public function store(RequestInterface $request): JsonResponse
{
    $dto = UserDTO::fromRequest($request);
}
```

### From Model

```php
$user = new User([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto = UserDTO::fromModel($user);
```

Beware that the fields in the `$hidden` property of the `Model` won't be used for the `DTO`.

### From Artisan Commands

You have three ways of creating a `DTO` instance from an `Artisan Command`:

#### From the Command Arguments

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

#### From the Command Options

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

#### From the Command Arguments and Options

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

## Accessing DTO data

After you create your `DTO` instance, you can access any properties like an `object`:

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

If you pass properties that are not listed in the `rules` method of your `DTO`, this data will be ignored and won't be available in your `DTO`:

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
    'username' => 'john_doe', 
]);

$dto->username; // THIS WON'T BE AVAILABLE IN YOUR DTO
```

## Defining Default Values

Sometimes we can have properties that are optional and that can have default values. You can define the default values for
your `DTO` properties in the `defaults` function:

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

With the `DTO` definition above you could run:

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A'
]);

$dto->username; // 'deeka_wong'
```

## Converting DTO data

You can convert your DTO to some formats:

### To array

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

### To JSON string

```php
$dto = UserDTO::fromArray([
    'name' => 'Deeka Wong',
    'email' => 'deeka@example.com',
    'password' => 'D3Crft!@1b2A',
]);

$dto->toJson();
// '{"name":"Deeka Wong","email":"deeka@example.com","password":"D3Crft!@1b2A"}'

$dto->toJson(true); // YOU CAN CALL IT LIKE THIS TO PRETTY PRINT YOUR JSON
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

## Customizing Error Messages, Attributes and Exceptions

You can define custom messages and attributes implementing the `messages` and `attributes` methods in your `DTO` class:

```php
/**
 * Defines the custom messages for validator errors.
 */
protected function messages() {
    return [];
}

/**
 * Defines the custom attributes for validator errors.
 */
protected function attributes() {
    return [];
}
```

## Type Casting

You can easily cast your DTO properties by defining a casts method in your DTO:

```php
/**
 * Defines the type casting for the properties of the DTO.
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

## Available Types

### Array

For JSON strings, it will convert into an array, for other types, it will wrap them in an array.

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

This accepts any value accepted by the `Carbon` constructor. If an invalid value is found it will throw a
`\FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast(),
    ];
}
```

You can also pass a timezone when defining the cast if you need that will be used when casting the value.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon'),
    ];
}
```

You can also pass a format when defining the cast to be used to cast the value. If the property has a different format than
the specified it will throw a `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### CarbonImmutable

This accepts any value accepted by the `CarbonImmutable` constructor. If an invalid value is found it will throw a
`\FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast(),
    ];
}
```

You can also pass a timezone when defining the cast if you need that will be used when casting the value.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon'),
    ];
}
```

You can also pass a format when defining the cast to be used to cast the value. If the property has a different format than
the specified it will throw a `\FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception.

```php
protected function casts(): array
{
    return [
        'property' => new CarbonImmutableCast('Europe/Lisbon', 'Y-m-d'),
    ];
}
```

### Collection

For JSON strings, it will convert into an array first, before wrapping it into a `Collection` object.

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(),
    ];
}
```

If you want to cast all the elements inside the `Collection`, you can pass a `Castable` to the `CollectionCast`
constructor. Let's say that you want to convert all the items inside the `Collection` into integers:

```php
protected function casts(): array
{
    return [
        'property' => new CollectionCast(new IntegerCast()),
    ];
}
```

This works with all `Castable`, including `DTOCast` and `ModelCast` for nested data.

### DTO

This works with arrays and JSON strings. This will validate the data and also cast the data for the given DTO.

This will throw a `Illuminate\Validation\ValidationException` exception if the data is not valid for the DTO.

This will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception if the property is not a valid
array or valid JSON string.

This will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` exception if the class passed to the
`DTOCast` constructor is not a `ValidatedDTO` instance.

```php
protected function casts(): array
{
    return [
        'property' => new DTOCast(UserDTO::class),
    ];
}
```

### Float

If a not numeric value is found, it will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception.

```php
protected function casts(): array
{
    return [
        'property' => new FloatCast(),
    ];
}
```

### Integer

If a not numeric value is found, it will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception.

```php
protected function casts(): array
{
    return [
        'property' => new IntegerCast(),
    ];
}
```

### Model

This works with arrays and JSON strings.

This will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception if the property is not a valid
array or valid JSON string.

This will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastTargetException` exception if the class passed to the
`ModelCast` constructor is not a `Model` instance.

```php
protected function casts(): array
{
    return [
        'property' => new ModelCast(User::class),
    ];
}
```

### Object

This works with arrays and JSON strings.

This will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException` exception if the property is not a valid
array or valid JSON string.

```php
protected function casts(): array
{
    return [
        'property' => new ObjectCast(),
    ];
}
```

### String

If the data can't be converted into a string, this will throw a `FriendsOfHyperf\ValidatedDTO\Exception\CastException`
exception.

```php
protected function casts(): array
{
    return [
        'property' => new StringCast(),
    ];
}
```

## Create Your Own Type Cast

You can easily create new `Castable` types for your project by implementing the `FriendsOfHyperf\ValidatedDTO\Casting\Castable`
interface. This interface has a single method that must be implemented:

```php
/**
 * Casts the given value.
 *
 * @param  string  $property
 * @param  mixed  $value
 * @return mixed
 */
public function cast(string $property, mixed $value): mixed;
```

Let's say that you have a `URLWrapper` class in your project, and you want that when passing a URL into your
`DTO` it will always return a `URLWrapper` instance instead of a simple string:

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

Then you could apply this to your DTO:

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

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
