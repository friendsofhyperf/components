# OpenAI Client

此組件將 [openai-php/client](https://github.com/openai-php/client) 集成到 Hyperf，
向依賴注入容器註冊上游客户端，並提供靜態門面。

## 依賴要求

此包面向 Hyperf 3.2，依賴 `hyperf/config`、`hyperf/di`、`hyperf/guzzle` 和 0.10.0 或更高版本的
`openai-php/client`，未聲明可選依賴。

## 安裝

```shell
composer require friendsofhyperf/openai-client
```

發佈配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

此命令會創建 `config/autoload/openai.php`。

## 配置

發佈的配置文件會讀取以下環境變量：

```env
OPENAI_BASE_URI=api.openai.com/v1
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=
OPENAI_REQUEST_TIMEOUT=30
```

| 配置項 | 默認值 | 説明 |
| --- | --- | --- |
| `openai.base_uri` | `api.openai.com/v1` | 傳給上游客户端工廠的基礎 URI。 |
| `openai.api_key` | 空字符串 | 用於 Bearer 身份驗證的 API 密鑰。 |
| `openai.organization` | `null` | 可選的 OpenAI 組織標識。 |
| `openai.request_timeout` | `30` | 傳給 Hyperf Guzzle 客户端的超時時間，單位為秒。 |

## 容器綁定與運行行為

組件會將 `OpenAI\Client` 和 `OpenAI\Contracts\ClientContract` 綁定到同一個
客户端實例。工廠會：

- 通過 `Hyperf\Guzzle\ClientFactory` 創建 HTTP 客户端；
- 應用配置的基礎 URI、API 密鑰、組織和請求超時時間；
- 發送 `OpenAI-Beta: assistants=v2` 請求頭。

API 密鑰必須是字符串，組織必須是 `null` 或字符串。類型不正確時會拋出
`FriendsOfHyperf\OpenAi\Exception\ApiKeyIsMissing`。空 API 密鑰仍是字符串，因此不會
觸發此異常；API 請求會改為因身份驗證失敗而報錯。

## 使用

### 容器

可以從容器解析契約或具體客户端。資源方法、請求參數和響應對象由已安裝的
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

`FriendsOfHyperf\OpenAi\Facade\OpenAI` 會將靜態調用轉發給容器綁定的 `ClientContract`。

```php
use FriendsOfHyperf\OpenAi\Facade\OpenAI;

$models = OpenAI::models()->list();
```

## Azure 與自定義客户端

組件工廠始終配置 Bearer 身份驗證，且未提供自定義請求頭或查詢參數的配置。
Azure OpenAI 等需要 `api-key` 請求頭和 `api-version` 查詢參數的服務，必須使用
上游工廠手動創建客户端：

```php
use OpenAI;

$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

手動創建的客户端不會自動註冊到 Hyperf 容器。由於 Azure 基礎 URI 已包含部署，
因此針對該部署的調用無需傳入 `model` 參數。

## 上游 API 指南

有關支持的資源和使用示例，請參閲與已安裝版本匹配的
[openai-php/client 文檔](https://github.com/openai-php/client)。
