# gRPC Validation

Validate a protobuf request before a Hyperf gRPC service method runs.

## Installation

```shell
composer require friendsofhyperf/grpc-validation
```

The component targets Hyperf 3.2 and requires `hyperf/context`, `hyperf/di`,
`hyperf/grpc-server`, and `hyperf/validation`. Composer installs these dependencies, and
the package's `ConfigProvider` automatically registers the validation aspect. There is no
component configuration file to publish.

## Basic Usage

Add `Validation` to a gRPC service method and define Hyperf validation rules for the
protobuf message fields:

```php
<?php

declare(strict_types=1);

use FriendsOfHyperf\GrpcValidation\Annotation\Validation;

final class GreeterService
{
    #[Validation(
        rules: [
            'name' => 'required|string|max:10',
            'message' => 'required|string|max:500',
        ],
        messages: [
            'name.required' => 'The name field is required.',
        ],
    )]
    public function sayHello(HiUser $user): HiReply
    {
        $reply = new HiReply();
        $reply->setMessage('Hello World');
        $reply->setUser($user);

        return $reply;
    }
}
```

`HiUser` and `HiReply` in this example are project-specific classes generated from your
`.proto` definitions. The component validates the first
`Google\Protobuf\Internal\Message` argument passed to the annotated method.

## Annotation Options

| Option | Type | Default | Behavior |
| --- | --- | --- | --- |
| `rules` | `array` | `[]` | Hyperf validation rules used when no resolvable `formRequest` is provided. |
| `messages` | `array` | `[]` | Custom validation messages used with `rules`. |
| `formRequest` | `string` | `''` | A container-resolvable `Hyperf\Validation\Request\FormRequest` class. Its `rules()` and `messages()` methods replace the inline values. |
| `scene` | `string` | `''` | Passed to `FormRequest::scene()`. When empty, the annotated method name is used. |
| `resolve` | `bool` | `true` | When `true`, failed validation immediately throws a gRPC validation exception. |

## Using a Form Request

Pass a Form Request class when rules should be shared. The class must be resolvable from
the container:

```php
use App\Request\SayHelloRequest;
use FriendsOfHyperf\GrpcValidation\Annotation\Validation;

final class GreeterService
{
    #[Validation(formRequest: SayHelloRequest::class, scene: 'create')]
    public function sayHello(HiUser $user): HiReply
    {
        // ...
    }
}
```

The component calls `scene()`, then reads `rules()` and `messages()`. It does not execute
the complete Form Request validation lifecycle, so methods such as `authorize()`,
`attributes()`, and `withValidator()` are not used. It also does not call Form Request's
protected scene-filtering method. To make `scene` affect the selected rules, implement
that selection in `rules()`, for example by reading `getScene()`.

If `formRequest` is not set or cannot be resolved from the container, the component falls
back to the annotation's inline `rules` and `messages`.

## Validation Behavior

- The protobuf message is converted with `serializeToJsonString()` and decoded to an
  array before validation.
- Validation runs only when rules, a protobuf message argument, and non-empty decoded
  data are all present. In particular, an entirely empty protobuf message serializes to
  `{}`, decodes to an empty array, and skips validation.
- When validation runs, the decoded data is stored in Hyperf context under the protobuf
  message class, and the validator is stored under `Hyperf\Contract\ValidatorInterface`.
- With the default `resolve: true`, a failure throws
  `FriendsOfHyperf\GrpcValidation\Exception\ValidationException`. It extends
  `Hyperf\GrpcServer\Exception\GrpcException`, uses the first validation error as its
  message, has code `422`, and keeps the Hyperf validation exception as its previous
  exception.
- With `resolve: false`, the component creates and stores the validator but does not
  automatically check it or throw on failure.
