# OAuth2 伺服器

基於 [league/oauth2-server](https://oauth2.thephpleague.com/) 的 Hyperf 框架完整 OAuth2 伺服器實現。

## 功能特性

- 完整的 OAuth2 伺服器實現，支援：
  - 客戶端憑證授權 (Client Credentials Grant)
  - 密碼授權 (Password Grant)
  - 刷新令牌授權 (Refresh Token Grant)
  - 授權碼授權 (Authorization Code Grant，支援 PKCE)
- 內置客戶端管理命令
- 多種存儲後端 (Eloquent ORM)
- 可自定義的令牌生命週期
- 作用域管理
- 事件驅動架構

## 安裝

### 1. 通過 Composer 安裝

```bash
composer require friendsofhyperf/oauth2-server
```

### 2. 發布配置

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

在 `config/autoload/oauth2-server.php` 中配置 OAuth2 伺服器：

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
| `oauth2:create-client` | 創建新的 OAuth2 客戶端 |
| `oauth2:delete-client` | 刪除 OAuth2 客戶端 |
| `oauth2:generate-keypair` | 生成私鑰/公鑰對 |
| `oauth2:list-clients` | 列出所有 OAuth2 客戶端 |
| `oauth2:update-client` | 更新 OAuth2 客戶端 |

### 創建客戶端

創建授權碼授權的客戶端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的網頁應用" \
    --redirect-uri="https://myapp.com/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

創建密碼授權的客戶端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的移動應用" \
    --grant-type="password" \
    --grant-type="refresh_token"
```

創建客戶端憑證授權的客戶端：

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
- `client_id`: 客戶端 ID
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

### 1. 客戶端憑證授權

用於伺服器到伺服器認證：

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

### 2. 密碼授權

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

### 3. 授權碼授權

用於需要用戶交互的網頁應用：

**步驟1：重定向用戶到授權端點**

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

### 4. 刷新令牌授權

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

## 發起認證請求

在 Authorization 頭中包含訪問令牌：

```bash
curl -X GET http://your-server/api/user \
    -H "Authorization: Bearer your-access-token"
```

## 事件系統

組件會分發以下事件，您可以監聽：

- `AuthorizationRequestResolveEvent`: 當授權請求需要用戶批准時
- `UserResolveEvent`: 當為密碼授權解析用戶時
- `ScopeResolveEvent`: 當解析作用域時
- `TokenRequestResolveEvent`: 當處理令牌請求時

### 事件監聽器示例

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Oauth2\Server\Event\UserResolveEvent;
use Hyperf\Event\Annotation\Listener;

#[Listener]
class UserResolveListener
{
    public function listen(): array
    {
        return [
            UserResolveEvent::class,
        ];
    }

    public function process(object $event): void
    {
        // 驗證用戶憑據並返回用戶ID
        if ($event->getUsername() === 'admin' && $event->getPassword() === 'secret') {
            $event->setUserId('1');
        }
    }
}
```

## 數據庫表

該包創建以下表：

- `oauth_clients`: OAuth2 客戶端
- `oauth_access_tokens`: 訪問令牌
- `oauth_refresh_tokens`: 刷新令牌
- `oauth_auth_codes`: 授權碼
- `oauth_personal_access_clients`: 個人訪問客戶端

## 自定義

### 自定義用戶提供程序

通過監聽 `UserResolveEvent` 實現您自己的用戶解析邏輯。

### 自定義作用域管理

監聽 `ScopeResolveEvent` 以實現自定義作用域邏輯。

### 自定義令牌存儲

擴展存儲庫類以實現自定義存儲後端。

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
# 創建測試客戶端
php bin/hyperf.php oauth2:create-client \
    --name="測試客戶端" \
    --redirect-uri="http://localhost:3000/callback" \
    --grant-type="authorization_code" \
    --grant-type="password" \
    --grant-type="refresh_token"

# 列出所有客戶端
php bin/hyperf.php oauth2:list-clients

# 清除過期令牌
php bin/hyperf.php oauth2:clear-expired-tokens
```

## 錯誤處理

常見錯誤響應：

- `invalid_client`: 客戶端認證失敗
- `invalid_grant`: 無效的授權許可
- `invalid_request`: 缺少必需參數
- `invalid_scope`: 請求的作用域無效
- `unsupported_grant_type`: 不支持的授權類型
- `server_error`: 內部伺服器錯誤

## 許可證

MIT