# OAuth2 服務器

基於 [league/oauth2-server](https://oauth2.thephpleague.com/) 的 Hyperf 框架完整 OAuth2 服務器實現。

## 功能特性

- 完整的 OAuth2 服務器實現，支持：
  - 客户端憑證授權 (Client Credentials Grant)
  - 密碼授權 (Password Grant)
  - 刷新令牌授權 (Refresh Token Grant)
  - 授權碼授權 (Authorization Code Grant，支持 PKCE)
  - 設備碼授權 (Device Code Grant)
  - 隱式授權 (Implicit Grant)
- 內置客户端管理命令
- 多種存儲後端 (Eloquent ORM)
- 可自定義的令牌生命週期
- 作用域管理
- 事件驅動架構
- 工廠模式實現
- 類型安全的值對象和枚舉
- 完整的錯誤處理和日誌記錄

## 安裝

### 1. 通過 Composer 安裝

```bash
composer require friendsofhyperf/oauth2-server
```

### 2. 發佈配置

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/oauth2-server
```

### 3. 生成加密密鑰

```bash
# 生成私鑰/公鑰對
php bin/hyperf.php oauth2:generate-keypair
```

這將生成：
- `storage/oauth2/private.key` - 用於簽名令牌的私鑰
- `storage/oauth2/public.key` - 用於驗證令牌的公鑰

### 4. 運行數據庫遷移

```bash
php bin/hyperf.php migrate
```

## 配置

在 `config/autoload/oauth2-server.php` 中配置 OAuth2 服務器：

```php
<?php

return [
    'authorization_server' => [
        'private_key' => env('OAUTH2_PRIVATE_KEY', 'storage/oauth2/private.key'),
        'private_key_passphrase' => env('OAUTH2_PRIVATE_KEY_PASSPHRASE'),
        'encryption_key' => env('OAUTH2_ENCRYPTION_KEY'),
        'encryption_key_type' => EncryptionKeyType::from(env('OAUTH2_ENCRYPTION_KEY_TYPE', 'plain')),
        'response_type' => BearerTokenResponse::class,
        'revoke_refresh_tokens' => true,
        'access_token_ttl' => new DateInterval('PT1H'),
        'auth_code_ttl' => new DateInterval('PT10M'),
        'refresh_token_ttl' => new DateInterval('P1M'),
        'enable_client_credentials_grant' => true,
        'enable_password_grant' => true,
        'enable_refresh_token_grant' => true,
        'enable_auth_code_grant' => true,
        'enable_implicit_grant' => false,
        'require_code_challenge_for_public_clients' => true,
        'persist_access_tokens' => true,
    ],
    'resource_server' => [
        'public_key' => env('OAUTH2_PUBLIC_KEY', 'storage/oauth2/public.key'),
        'jwt_leeway' => null,
    ],
    'scopes' => [
        'available' => ['read', 'write', 'admin'],
        'default' => ['read'],
    ],
];
```

## 環境變量

在 `.env` 文件中設置以下環境變量：

```bash
# OAuth2 密鑰
OAUTH2_PRIVATE_KEY=storage/oauth2/private.key
OAUTH2_PUBLIC_KEY=storage/oauth2/public.key
OAUTH2_PRIVATE_KEY_PASSPHRASE=
OAUTH2_ENCRYPTION_KEY=your-encryption-key-here

# 可選
OAUTH2_ENCRYPTION_KEY_TYPE=plain
```

## 可用命令

| 命令 | 描述 |
|---------|-------------|
| `oauth2:clear-expired-tokens` | 清除過期的訪問/刷新令牌 |
| `oauth2:create-client` | 創建新的 OAuth2 客户端 |
| `oauth2:delete-client` | 刪除 OAuth2 客户端 |
| `oauth2:generate-keypair` | 生成私鑰/公鑰對 |
| `oauth2:list-clients` | 列出所有 OAuth2 客户端 |
| `oauth2:update-client` | 更新 OAuth2 客户端 |

### 創建客户端

創建授權碼授權的客户端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的網頁應用" \
    --redirect-uri="https://myapp.com/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

創建密碼授權的客户端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的移動應用" \
    --grant-type="password" \
    --grant-type="refresh_token"
```

創建客户端憑證授權的客户端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的API服務" \
    --grant-type="client_credentials"
```

## API 端點

### 授權端點

`GET /oauth/authorize`

用於授權碼授權流程。參數：
- `response_type`: 必須是 `code`
- `client_id`: 客户端 ID
- `redirect_uri`: 必須與註冊的回調 URI 匹配
- `scope`: 空格分隔的作用域列表
- `state`: CSRF 保護令牌
- `code_challenge`: PKCE 代碼挑戰
- `code_challenge_method`: PKCE 方法（通常是 `S256`）

### 令牌端點

`POST /oauth/token`

用於交換授權碼獲取訪問令牌或使用其他授權類型。

### 受保護資源

使用 `ResourceServerMiddleware` 保護路由：

```php
use FriendsOfHyperf\Oauth2\Server\Middleware\ResourceServerMiddleware;

Router::addGroup('/api', function () {
    Router::get('user', [UserController::class, 'index']);
    Router::post('posts', [PostController::class, 'store']);
})->add(ResourceServerMiddleware::class);
```

## 授權類型

### 1. 客户端憑證授權 (Client Credentials Grant)

用於服務器到服務器認證：

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "client_credentials",
        "client_id": "your-client-id",
        "client_secret": "your-client-secret",
        "scope": "read write"
    }'
```

**適用場景：**
- 微服務間通信
- API 密鑰認證
- 系統集成

### 2. 密碼授權 (Password Grant)

用於可信應用（移動應用、SPA）：

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "password",
        "client_id": "your-client-id",
        "client_secret": "your-client-secret",
        "username": "user@example.com",
        "password": "password",
        "scope": "read write"
    }'
```

**適用場景：**
- 移動應用
- 單頁應用 (SPA)
- 可信的第三方應用

**注意：** 此授權方式需要高度信任客户端，謹慎使用。

### 3. 授權碼授權 (Authorization Code Grant)

用於需要用户交互的網頁應用：

**步驟1：重定向用户到授權端點**

```
https://your-server/oauth/authorize?response_type=code&client_id=your-client-id&redirect_uri=https://myapp.com/callback&scope=read&state=random-state&code_challenge=challenge&code_challenge_method=S256
```

**步驟2：交換授權碼獲取令牌**

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "authorization_code",
        "client_id": "your-client-id",
        "client_secret": "your-client-secret",
        "redirect_uri": "https://myapp.com/callback",
        "code_verifier": "verifier",
        "code": "authorization-code-from-redirect"
    }'
```

**PKCE 支持：**
- `code_challenge`: 生成的代碼挑戰
- `code_challenge_method`: S256 或 plain
- `code_verifier`: 用於驗證的原始代碼

**適用場景：**
- 網頁應用
- 需要用户授權的應用
- 安全要求高的場景

### 4. 刷新令牌授權 (Refresh Token Grant)

獲取新的訪問令牌：

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "refresh_token",
        "client_id": "your-client-id",
        "client_secret": "your-client-secret",
        "refresh_token": "your-refresh-token",
        "scope": "read write"
    }'
```

**適用場景：**
- 延長用户會話
- 避免頻繁重新登錄
- 移動應用後台服務

### 5. 設備碼授權 (Device Code Grant)

用於無輸入設備（如智能電視、IoT設備）：

**步驟1：請求設備碼**

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "device_code",
        "client_id": "your-client-id",
        "scope": "read"
    }'
```

**步驟2：用户在其他設備上授權**

用户需要在手機或電腦上訪問顯示的授權URL完成授權。

**步驟3：輪詢令牌**

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "device_code",
        "client_id": "your-client-id",
        "device_code": "device-code-from-step1"
    }'
```

**適用場景：**
- 智能電視應用
- IoT 設備
- 無輸入設備的應用

### 6. 隱式授權 (Implicit Grant)

已不推薦使用，但在某些舊系統中仍可能遇到：

```
https://your-server/oauth/authorize?response_type=token&client_id=your-client-id&redirect_uri=https://myapp.com/callback&scope=read&state=random-state
```

**注意：** 此授權方式已被 OAuth 2.1 棄用，建議使用授權碼授權 + PKCE。

## 工廠模式實現

組件使用工廠模式創建服務器實例，提供了更好的靈活性和可測試性：

### 授權服務器工廠

```php
use FriendsOfHyperf\Oauth2\Server\Factory\AuthorizationServerFactory;
use Hyperf\Di\Annotation\Inject;

class YourController
{
    #[Inject]
    private AuthorizationServerFactory $authorizationServerFactory;

    public function handleAuthorization()
    {
        // 構建授權服務器
        $authorizationServer = $this->authorizationServerFactory->build();
        
        // 使用授權服務器處理請求
        // ...
    }
}
```

### 資源服務器工廠

```php
use FriendsOfHyperf\Oauth2\Server\Factory\ResourceServerFactory;
use Hyperf\Di\Annotation\Inject;

class YourController
{
    #[Inject]
    private ResourceServerFactory $resourceServerFactory;

    public function getProtectedData()
    {
        // 構建資源服務器
        $resourceServer = $this->resourceServerFactory->build();
        
        // 驗證訪問令牌
        $accessToken = $resourceServer->validateAuthenticatedRequest($request);
        
        // 獲取令牌信息
        $tokenId = $accessToken->getAttribute('oauth_access_token_id');
        $userId = $accessToken->getAttribute('oauth_user_id');
        
        // 返回受保護的數據
        return ['user_id' => $userId];
    }
}
```

### 配置工廠

```php
use FriendsOfHyperf\Oauth2\Server\Factory\ConfigFactory;
use Hyperf\Di\Annotation\Inject;

class YourController
{
    #[Inject]
    private ConfigFactory $configFactory;

    public function getConfig()
    {
        // 獲取 OAuth2 配置
        $config = $this->configFactory->create();
        
        // 訪問配置項
        $accessTokenTtl = $config->get('authorization_server.access_token_ttl');
        $encryptionKey = $config->get('authorization_server.encryption_key');
        
        return $config;
    }
}
```

## 發起認證請求

在 Authorization 頭中包含訪問令牌：

```bash
curl -X GET http://your-server/api/user \
    -H "Authorization: Bearer your-access-token"
```

## 事件系統

組件提供了完整的事件系統，允許您自定義 OAuth2 流程的各個方面：

### 可用事件

| 事件類 | 描述 | 使用場景 |
|--------|------|----------|
| `AuthorizationRequestResolveEvent` | 當授權請求需要用户批准時觸發 | 實現自定義授權邏輯、顯示授權頁面 |
| `UserResolveEvent` | 當為密碼授權解析用户時觸發 | 實現自定義用户認證邏輯 |
| `ScopeResolveEvent` | 當解析作用域時觸發 | 實現自定義作用域驗證和過濾 |
| `TokenRequestResolveEvent` | 當處理令牌請求時觸發 | 記錄令牌發放、添加自定義響應頭 |
| `PreSaveClientEvent` | 在保存客户端之前觸發 | 驗證客户端數據、添加默認值 |

### 事件監聽器示例

#### 1. 自定義用户認證

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\UserResolveEvent;
use FriendsOfHyperf\Oauth2\Server\Model\UserInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Di\Annotation\Inject;
use App\Service\UserService;

#[Listener]
class UserResolveListener
{
    #[Inject]
    private UserService $userService;

    public function listen(): array
    {
        return [
            UserResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof UserResolveEvent) {
            return;
        }

        // 驗證用户憑據
        $user = $this->userService->authenticate($event->getUsername(), $event->getPassword());
        
        if ($user) {
            // 設置用户實體
            $userEntity = new UserInterface();
            $userEntity->setIdentifier($user->id);
            $event->setUser($userEntity);
        }
    }
}
```

#### 2. 自定義授權處理

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\AuthorizationRequestResolveEvent;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Listener]
class AuthorizationRequestResolveListener
{
    public function listen(): array
    {
        return [
            AuthorizationRequestResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof AuthorizationRequestResolveEvent) {
            return;
        }

        // 檢查客户端是否被允許訪問請求的作用域
        if (!$this->isScopeAllowed($event->getClient(), $event->getScopes())) {
            // 返回自定義錯誤響應
            $response = $this->createErrorResponse('invalid_scope', 'Requested scope is not allowed');
            $event->setResponse($response);
            return;
        }

        // 自動批准可信客户端的請求
        if ($this->isTrustedClient($event->getClient())) {
            $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
        }
    }

    private function isScopeAllowed(ClientInterface $client, array $scopes): bool
    {
        // 實現作用域驗證邏輯
        return true;
    }

    private function isTrustedClient(ClientInterface $client): bool
    {
        // 實現可信客户端檢查邏輯
        return $client->getName() === 'Trusted App';
    }
}
```

#### 3. 自定義作用域解析

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\ScopeResolveEvent;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Event\Annotation\Listener;

#[Listener]
class ScopeResolveListener
{
    public function listen(): array
    {
        return [
            ScopeResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof ScopeResolveEvent) {
            return;
        }

        // 根據用户角色動態調整作用域
        $userScopes = $this->getUserScopes($event->getUserId());
        $filteredScopes = [];

        foreach ($event->getRequestedScopes() as $scope) {
            if (in_array((string) $scope, $userScopes)) {
                $filteredScopes[] = $scope;
            }
        }

        $event->setResolvedScopes($filteredScopes);
    }

    private function getUserScopes(string $userId): array
    {
        // 實現用户作用域獲取邏輯
        return ['read', 'write'];
    }
}
```

#### 4. 令牌請求記錄

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\TokenRequestResolveEvent;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Di\Annotation\Inject;
use App\Service\AuditService;

#[Listener]
class TokenRequestResolveListener
{
    #[Inject]
    private AuditService $auditService;

    public function listen(): array
    {
        return [
            TokenRequestResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TokenRequestResolveEvent) {
            return;
        }

        // 記錄令牌發放
        $response = $event->getResponse();
        $responseData = json_decode((string) $response->getBody(), true);
        
        if (isset($responseData['access_token'])) {
            $this->auditService->logTokenIssued([
                'access_token' => $responseData['access_token'],
                'token_type' => $responseData['token_type'] ?? 'Bearer',
                'expires_in' => $responseData['expires_in'] ?? null,
                'scope' => $responseData['scope'] ?? null,
                'client_id' => $responseData['client_id'] ?? null,
                'user_id' => $responseData['user_id'] ?? null,
            ]);
        }
    }
}
```

## 數據庫表

該包創建以下表：

- `oauth_clients`: OAuth2 客户端
- `oauth_access_tokens`: 訪問令牌
- `oauth_refresh_tokens`: 刷新令牌
- `oauth_auth_codes`: 授權碼
- `oauth_personal_access_clients`: 個人訪問客户端

## 自定義

### 自定義用户提供程序

通過監聽 `UserResolveEvent` 實現您自己的用户解析邏輯。

### 自定義作用域管理

監聽 `ScopeResolveEvent` 以實現自定義作用域邏輯。

### 自定義令牌存儲

擴展存儲庫類以實現自定義存儲後端。

### 自定義客户端驗證

通過監聽 `PreSaveClientEvent` 實現客户端數據驗證和默認值設置：

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\PreSaveClientEvent;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\RedirectUri;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Event\Annotation\Listener;

#[Listener]
class PreSaveClientListener
{
    public function listen(): array
    {
        return [
            PreSaveClientEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof PreSaveClientEvent) {
            return;
        }

        $client = $event->getClient();
        
        // 設置默認作用域
        if (empty($client->getScopes())) {
            $client->setScopes(new Scope('read'));
        }
        
        // 驗證重定向URI
        foreach ($client->getRedirectUris() as $uri) {
            if (!$this->isValidRedirectUri($uri)) {
                throw new \InvalidArgumentException('Invalid redirect URI');
            }
        }
        
        // 設置默認授權類型
        if (empty($client->getGrants())) {
            $client->setGrants(new Grant('client_credentials'));
        }
    }

    private function isValidRedirectUri(RedirectUri $uri): bool
    {
        // 實現URI驗證邏輯
        return str_starts_with((string) $uri, 'https://');
    }
}
```

### 自定義錯誤處理

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\TokenRequestResolveEvent;
use League\OAuth2\Server\Exception\OAuthServerException;
use Hyperf\Event\Annotation\Listener;
use Hyperf\HttpMessage\Stream\SwooleStream;

#[Listener]
class CustomErrorHandler
{
    public function listen(): array
    {
        return [
            TokenRequestResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TokenRequestResolveEvent) {
            return;
        }

        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();
        
        // 自定義錯誤響應格式
        if ($statusCode >= 400) {
            $body = json_decode((string) $response->getBody(), true);
            
            $customError = [
                'error' => $body['error'] ?? 'server_error',
                'error_description' => $body['error_description'] ?? 'An error occurred',
                'error_code' => $this->getErrorCode($body['error'] ?? 'server_error'),
                'timestamp' => time(),
                'request_id' => $this->generateRequestId(),
            ];
            
            $newResponse = $response->withBody(new SwooleStream(json_encode($customError)));
            $event->setResponse($newResponse);
        }
    }

    private function getErrorCode(string $error): string
    {
        // 實現錯誤代碼映射
        return match($error) {
            'invalid_client' => 'AUTH_001',
            'invalid_grant' => 'AUTH_002',
            'invalid_scope' => 'AUTH_003',
            default => 'AUTH_999',
        };
    }

    private function generateRequestId(): string
    {
        // 生成請求ID
        return uniqid('oauth_', true);
    }
}
```

### 自定義令牌響應

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\TokenRequestResolveEvent;
use Hyperf\Event\Annotation\Listener;
use Hyperf\HttpMessage\Stream\SwooleStream;

#[Listener]
class CustomTokenResponseListener
{
    public function listen(): array
    {
        return [
            TokenRequestResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TokenRequestResolveEvent) {
            return;
        }

        $response = $event->getResponse();
        $body = json_decode((string) $response->getBody(), true);
        
        if (isset($body['access_token'])) {
            // 添加自定義字段
            $customResponse = array_merge($body, [
                'token_type' => 'Bearer',
                'expires_in' => $body['expires_in'] ?? 3600,
                'issued_at' => time(),
                'user_info' => $this->getUserInfo($body),
                'permissions' => $this->getPermissions($body['scope'] ?? ''),
            ]);
            
            $newResponse = $response->withBody(new SwooleStream(json_encode($customResponse)));
            $event->setResponse($newResponse);
        }
    }

    private function getUserInfo(array $tokenData): array
    {
        // 獲取用户信息
        return [
            'id' => $tokenData['user_id'] ?? null,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
    }

    private function getPermissions(string $scope): array
    {
        // 根據作用域獲取權限
        $permissions = [];
        $scopes = explode(' ', $scope);
        
        foreach ($scopes as $scope) {
            $permissions = array_merge($permissions, $this->scopeToPermissions($scope));
        }
        
        return array_unique($permissions);
    }

    private function scopeToPermissions(string $scope): array
    {
        // 作用域到權限的映射
        return match($scope) {
            'read' => ['user:read', 'post:read'],
            'write' => ['user:write', 'post:write'],
            'admin' => ['*'],
            default => [],
        };
    }
}
```

## 安全最佳實踐

1. 在生產環境中始終使用 HTTPS
2. 使用適當的文件權限安全存儲私鑰
3. 使用強加密密鑰
4. 為授權流程實施適當的 CSRF 保護
5. 嚴格驗證回調 URI
6. 使用短生命週期的訪問令牌和刷新令牌
7. 在令牌端點上實施速率限制
8. 記錄和監控令牌使用情況

## 測試

在開發過程中，您可以使用內置命令測試 OAuth2 流程：

```bash
# 創建測試客户端
php bin/hyperf.php oauth2:create-client \
    --name="測試客户端" \
    --redirect-uri="http://localhost:3000/callback" \
    --grant-type="authorization_code" \
    --grant-type="password" \
    --grant-type="refresh_token"

# 列出所有客户端
php bin/hyperf.php oauth2:list-clients

# 清除過期令牌
php bin/hyperf.php oauth2:clear-expired-tokens
```

## 錯誤處理

常見錯誤響應：

- `invalid_client`: 客户端認證失敗
- `invalid_grant`: 無效的授權許可
- `invalid_request`: 缺少必需參數
- `invalid_scope`: 請求的作用域無效
- `unsupported_grant_type`: 不支持的授權類型
- `server_error`: 內部服務器錯誤

## 許可證

MIT