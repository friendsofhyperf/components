# OpenAI Client

此元件將 [openai-php/client](https://github.com/openai-php/client) 整合到 Hyperf，
向依賴注入容器註冊上游客戶端，並提供靜態門面。

## 依賴要求

此包面向 Hyperf 3.2，依賴 `hyperf/config`、`hyperf/di`、`hyperf/guzzle` 和 0.10.0 或更高版本的
`openai-php/client`，未宣告可選依賴。

## 安裝

```shell
composer require friendsofhyperf/openai-client
```

釋出配置檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

此命令會建立 `config/autoload/openai.php`。

## 配置

釋出的配置檔案會讀取以下環境變數：

```env
OPENAI_BASE_URI=api.openai.com/v1
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=
OPENAI_REQUEST_TIMEOUT=30
```

| 配置項 | 預設值 | 說明 |
| --- | --- | --- |
| `openai.base_uri` | `api.openai.com/v1` | 傳給上游客戶端工廠的基礎 URI。 |
| `openai.api_key` | 空字串 | 用於 Bearer 身份驗證的 API 金鑰。 |
| `openai.organization` | `null` | 可選的 OpenAI 組織標識。 |
| `openai.request_timeout` | `30` | 傳給 Hyperf Guzzle 客戶端的超時時間，單位為秒。 |

## 容器繫結與執行行為

元件會將 `OpenAI\Client` 和 `OpenAI\Contracts\ClientContract` 繫結到同一個
客戶端例項。工廠會：

- 透過 `Hyperf\Guzzle\ClientFactory` 建立 HTTP 客戶端；
- 應用配置的基礎 URI、API 金鑰、組織和請求超時時間；
- 傳送 `OpenAI-Beta: assistants=v2` 請求頭。

API 金鑰必須是字串，組織必須是 `null` 或字串。型別不正確時會丟擲
`FriendsOfHyperf\OpenAi\Exception\ApiKeyIsMissing`。空 API 金鑰仍是字串，因此不會
觸發此異常；API 請求會改為因身份驗證失敗而報錯。

## 使用

### 容器

可以從容器解析契約或具體客戶端。資源方法、請求引數和響應物件由已安裝的
`openai-php/client` 版本提供。

```php
use OpenAI\Contracts\ClientContract;

$response = di(ClientContract::class)->chat()->create([
    'model' => 'YOUR_MODEL',
    'messages' => [
        ['role' => 'user', 'content' => 'Briefly explain dependency injection.'],
    ],
]);

echo $response->choices[0]->message->content;
```

### 門面

`FriendsOfHyperf\OpenAi\Facade\OpenAI` 會將靜態呼叫轉發給容器繫結的 `ClientContract`。

```php
use FriendsOfHyperf\OpenAi\Facade\OpenAI;

$models = OpenAI::models()->list();
```

## Azure 與自定義客戶端

元件工廠始終配置 Bearer 身份驗證，且未提供自定義請求頭或查詢引數的配置。
Azure OpenAI 等需要 `api-key` 請求頭和 `api-version` 查詢引數的服務，必須使用
上游工廠手動建立客戶端：

```php
use OpenAI;

$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

手動建立的客戶端不會自動註冊到 Hyperf 容器。由於 Azure 基礎 URI 已包含部署，
因此針對該部署的呼叫無需傳入 `model` 引數。

## 上游 API 指南

有關支援的資源和使用示例，請參閱與已安裝版本匹配的
[openai-php/client 文件](https://github.com/openai-php/client)。
