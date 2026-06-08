# gRPC Validation

在 Hyperf gRPC 服务方法执行前验证 protobuf 请求。

## 安装

```shell
composer require friendsofhyperf/grpc-validation
```

该组件面向 Hyperf 3.2，并依赖 `hyperf/context`、`hyperf/di`、`hyperf/grpc-server`
和 `hyperf/validation`。Composer 会安装这些依赖，包内的 `ConfigProvider` 会自动注册验证切面，
无需发布组件配置文件。

## 基本用法

在 gRPC 服务方法上添加 `Validation`，并为 protobuf 消息字段定义 Hyperf 验证规则：

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

示例中的 `HiUser` 和 `HiReply` 是项目根据 `.proto` 定义生成的类。组件会验证传给被注解方法的
第一个 `Google\Protobuf\Internal\Message` 参数。

## 注解选项

| 选项 | 类型 | 默认值 | 行为 |
| --- | --- | --- | --- |
| `rules` | `array` | `[]` | 未提供可解析的 `formRequest` 时使用的 Hyperf 验证规则。 |
| `messages` | `array` | `[]` | 与 `rules` 一起使用的自定义验证消息。 |
| `formRequest` | `string` | `''` | 可从容器解析的 `Hyperf\Validation\Request\FormRequest` 类，其 `rules()` 和 `messages()` 会替代注解内的值。 |
| `scene` | `string` | `''` | 传给 `FormRequest::scene()`；为空时使用被注解的方法名。 |
| `resolve` | `bool` | `true` | 为 `true` 时，验证失败会立即抛出 gRPC 验证异常。 |

## 使用 Form Request

需要复用规则时可传入 Form Request 类。该类必须能从容器解析：

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

组件会先调用 `scene()`，再读取 `rules()` 和 `messages()`。它不会执行完整的 Form Request
验证生命周期，因此不会使用 `authorize()`、`attributes()` 和 `withValidator()` 等方法，也不会调用
Form Request 受保护的场景规则筛选方法。若要让 `scene` 影响所选规则，请在 `rules()` 中自行选择，
例如读取 `getScene()`。

如果未设置 `formRequest`，或无法从容器解析它，组件会回退到注解内的 `rules` 和 `messages`。

## 验证行为

- protobuf 消息会先通过 `serializeToJsonString()` 转换并解码为数组，再进行验证。
- 只有规则、protobuf 消息参数和非空解码数据同时存在时才会验证。特别是，完全为空的 protobuf
  消息会序列化为 `{}`、解码为空数组，从而跳过验证。
- 执行验证时，解码数据会以 protobuf 消息类名为键存入 Hyperf 上下文，验证器会以
  `Hyperf\Contract\ValidatorInterface` 为键存入上下文。
- 使用默认的 `resolve: true` 时，验证失败会抛出
  `FriendsOfHyperf\GrpcValidation\Exception\ValidationException`。该异常继承
  `Hyperf\GrpcServer\Exception\GrpcException`，使用第一条验证错误作为消息，代码为 `422`，
  并将 Hyperf 验证异常保留为前一个异常。
- 使用 `resolve: false` 时，组件会创建并存储验证器，但不会自动检查它或在失败时抛出异常。
