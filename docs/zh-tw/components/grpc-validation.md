# gRPC Validation

在 Hyperf gRPC 服務方法執行前驗證 protobuf 請求。

## 安裝

```shell
composer require friendsofhyperf/grpc-validation
```

該元件面向 Hyperf 3.2，並依賴 `hyperf/context`、`hyperf/di`、`hyperf/grpc-server`
和 `hyperf/validation`。Composer 會安裝這些依賴，包內的 `ConfigProvider` 會自動註冊驗證切面，
無需釋出元件配置檔案。

## 基本用法

在 gRPC 服務方法上新增 `Validation`，併為 protobuf 訊息欄位定義 Hyperf 驗證規則：

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

示例中的 `HiUser` 和 `HiReply` 是專案根據 `.proto` 定義生成的類。元件會驗證傳給被註解方法的
第一個 `Google\Protobuf\Internal\Message` 引數。

## 註解選項

| 選項 | 型別 | 預設值 | 行為 |
| --- | --- | --- | --- |
| `rules` | `array` | `[]` | 未提供可解析的 `formRequest` 時使用的 Hyperf 驗證規則。 |
| `messages` | `array` | `[]` | 與 `rules` 一起使用的自定義驗證訊息。 |
| `formRequest` | `string` | `''` | 可從容器解析的 `Hyperf\Validation\Request\FormRequest` 類，其 `rules()` 和 `messages()` 會替代註解內的值。 |
| `scene` | `string` | `''` | 傳給 `FormRequest::scene()`；為空時使用被註解的方法名。 |
| `resolve` | `bool` | `true` | 為 `true` 時，驗證失敗會立即丟擲 gRPC 驗證異常。 |

## 使用 Form Request

需要複用規則時可傳入 Form Request 類。該類必須能從容器解析：

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

元件會先呼叫 `scene()`，再讀取 `rules()` 和 `messages()`。它不會執行完整的 Form Request
驗證生命週期，因此不會使用 `authorize()`、`attributes()` 和 `withValidator()` 等方法，也不會呼叫
Form Request 受保護的場景規則篩選方法。若要讓 `scene` 影響所選規則，請在 `rules()` 中自行選擇，
例如讀取 `getScene()`。

如果未設定 `formRequest`，或無法從容器解析它，元件會回退到註解內的 `rules` 和 `messages`。

## 驗證行為

- protobuf 訊息會先透過 `serializeToJsonString()` 轉換並解碼為陣列，再進行驗證。
- 只有規則、protobuf 訊息引數和非空解碼資料同時存在時才會驗證。特別是，完全為空的 protobuf
  訊息會序列化為 `{}`、解碼為空陣列，從而跳過驗證。
- 執行驗證時，解碼資料會以 protobuf 訊息類名為鍵存入 Hyperf 上下文，驗證器會以
  `Hyperf\Contract\ValidatorInterface` 為鍵存入上下文。
- 使用預設的 `resolve: true` 時，驗證失敗會丟擲
  `FriendsOfHyperf\GrpcValidation\Exception\ValidationException`。該異常繼承
  `Hyperf\GrpcServer\Exception\GrpcException`，使用第一條驗證錯誤作為訊息，程式碼為 `422`，
  並將 Hyperf 驗證異常保留為前一個異常。
- 使用 `resolve: false` 時，元件會建立並存儲驗證器，但不會自動檢查它或在失敗時丟擲異常。
