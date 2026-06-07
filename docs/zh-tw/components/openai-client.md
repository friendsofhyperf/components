# OpenAI Client

此元件將 [openai-php/client](https://github.com/openai-php/client) 整合至 Hyperf，
向依賴注入容器註冊上游用戶端，並提供靜態門面。

## 相依需求

此套件適用於 Hyperf 3.2，相依於 `hyperf/config`、`hyperf/di`、`hyperf/guzzle` 和
0.10.0 或更新版本的 `openai-php/client`，且未宣告選用相依套件。

## 安裝

```shell
composer require friendsofhyperf/openai-client
```

發布設定檔：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/openai-client
```

此指令會建立 `config/autoload/openai.php`。

## 設定

發布的設定檔會讀取以下環境變數：

```env
OPENAI_BASE_URI=api.openai.com/v1
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=
OPENAI_REQUEST_TIMEOUT=30
```

| 設定項目 | 預設值 | 說明 |
| --- | --- | --- |
| `openai.base_uri` | `api.openai.com/v1` | 傳給上游用戶端工廠的基礎 URI。 |
| `openai.api_key` | 空字串 | 用於 Bearer 驗證的 API 金鑰。 |
| `openai.organization` | `null` | 選用的 OpenAI 組織識別碼。 |
| `openai.request_timeout` | `30` | 傳給 Hyperf Guzzle 用戶端的逾時時間，單位為秒。 |

## 容器綁定與執行行為

元件會將 `OpenAI\Client` 和 `OpenAI\Contracts\ClientContract` 綁定至同一個
用戶端實例。工廠會：

- 透過 `Hyperf\Guzzle\ClientFactory` 建立 HTTP 用戶端；
- 套用設定的基礎 URI、API 金鑰、組織和請求逾時時間；
- 傳送 `OpenAI-Beta: assistants=v2` 請求標頭。

API 金鑰必須是字串，組織必須是 `null` 或字串。型別不正確時會擲出
`FriendsOfHyperf\OpenAi\Exception\ApiKeyIsMissing`。空 API 金鑰仍是字串，因此不會
觸發此例外；API 請求會改為因驗證失敗而報錯。

## 使用方式

### 容器

可以從容器解析契約或具體用戶端。資源方法、請求參數和回應物件由已安裝的
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

`FriendsOfHyperf\OpenAi\Facade\OpenAI` 會將靜態呼叫轉發給容器綁定的 `ClientContract`。

```php
use FriendsOfHyperf\OpenAi\Facade\OpenAI;

$models = OpenAI::models()->list();
```

## Azure 與自訂用戶端

元件工廠會固定設定 Bearer 驗證，且未提供自訂請求標頭或查詢參數的設定。
Azure OpenAI 等需要 `api-key` 請求標頭和 `api-version` 查詢參數的服務，必須使用
上游工廠手動建立用戶端：

```php
use OpenAI;

$client = OpenAI::factory()
    ->withBaseUri('{your-resource-name}.openai.azure.com/openai/deployments/{deployment-id}')
    ->withHttpHeader('api-key', '{your-api-key}')
    ->withQueryParam('api-version', '{version}')
    ->make();
```

手動建立的用戶端不會自動註冊至 Hyperf 容器。由於 Azure 基礎 URI 已包含部署，
因此針對該部署的呼叫不需傳入 `model` 參數。

## 上游 API 指南

有關支援的資源和使用範例，請參閱與已安裝版本相符的
[openai-php/client 文件](https://github.com/openai-php/client)。
