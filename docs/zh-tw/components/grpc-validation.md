# gRPC Validation

在 Hyperf gRPC 服務方法執行前驗證 protobuf 請求。

## 安裝

```shell
composer require friendsofhyperf/grpc-validation
```

此元件面向 Hyperf 3.2，並依賴 `hyperf/context`、`hyperf/di`、`hyperf/grpc-server`
和 `hyperf/validation`。Composer 會安裝這些依賴，套件內的 `ConfigProvider` 會自動註冊驗證切面，
不需發布元件設定檔。

## 基本用法

在 gRPC 服務方法上加入 `Validation`，並為 protobuf 訊息欄位定義 Hyperf 驗證規則：

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

範例中的 `HiUser` 和 `HiReply` 是專案根據 `.proto` 定義產生的類別。元件會驗證傳給已註解方法的
第一個 `Google\Protobuf\Internal\Message` 參數。

## 註解選項

| 選項 | 型別 | 預設值 | 行為 |
| --- | --- | --- | --- |
| `rules` | `array` | `[]` | 未提供可解析的 `formRequest` 時使用的 Hyperf 驗證規則。 |
| `messages` | `array` | `[]` | 與 `rules` 一同使用的自訂驗證訊息。 |
| `formRequest` | `string` | `''` | 可從容器解析的 `Hyperf\Validation\Request\FormRequest` 類別，其 `rules()` 和 `messages()` 會取代註解內的值。 |
| `scene` | `string` | `''` | 傳給 `FormRequest::scene()`；留空時使用已註解的方法名稱。 |
| `resolve` | `bool` | `true` | 為 `true` 時，驗證失敗會立即擲回 gRPC 驗證例外。 |

## 使用 Form Request

需要重複使用規則時可傳入 Form Request 類別。該類別必須能從容器解析：

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
Form Request 受保護的情境規則篩選方法。若要讓 `scene` 影響所選規則，請在 `rules()` 中自行選擇，
例如讀取 `getScene()`。

如果未設定 `formRequest`，或無法從容器解析它，元件會回退至註解內的 `rules` 和 `messages`。

## 驗證行為

- protobuf 訊息會先透過 `serializeToJsonString()` 轉換並解碼為陣列，再進行驗證。
- 只有規則、protobuf 訊息參數和非空解碼資料同時存在時才會驗證。特別是，完全空白的 protobuf
  訊息會序列化為 `{}`、解碼為空陣列，因而略過驗證。
- 執行驗證時，解碼資料會以 protobuf 訊息類別名稱為鍵存入 Hyperf 上下文，驗證器會以
  `Hyperf\Contract\ValidatorInterface` 為鍵存入上下文。
- 使用預設的 `resolve: true` 時，驗證失敗會擲回
  `FriendsOfHyperf\GrpcValidation\Exception\ValidationException`。該例外繼承
  `Hyperf\GrpcServer\Exception\GrpcException`，使用第一項驗證錯誤作為訊息，代碼為 `422`，
  並將 Hyperf 驗證例外保留為前一個例外。
- 使用 `resolve: false` 時，元件會建立並儲存驗證器，但不會自動檢查它或在失敗時擲回例外。
