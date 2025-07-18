# OAuth2 服务器

基于 [league/oauth2-server](https://oauth2.thephpleague.com/) 的 Hyperf 框架完整 OAuth2 服务器实现。

## 功能特性

- 完整的 OAuth2 服务器实现，支持：
  - 客户端凭证授权 (Client Credentials Grant)
  - 密码授权 (Password Grant)
  - 刷新令牌授权 (Refresh Token Grant)
  - 授权码授权 (Authorization Code Grant，支持 PKCE)
- 内置客户端管理命令
- 多种存储后端 (Eloquent ORM)
- 可自定义的令牌生命周期
- 作用域管理
- 事件驱动架构

## 安装

### 1. 通过 Composer 安装

```bash
composer require friendsofhyperf/oauth2-server
```

### 2. 发布配置

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/oauth2-server
```

### 3. 生成加密密钥

```bash
# 生成私钥/公钥对
php bin/hyperf.php oauth2:generate-keypair
```

这将生成：
- `storage/oauth2/private.key` - 用于签名令牌的私钥
- `storage/oauth2/public.key` - 用于验证令牌的公钥

### 4. 运行数据库迁移

```bash
php bin/hyperf.php migrate
```

## 配置

在 `config/autoload/oauth2-server.php` 中配置 OAuth2 服务器：

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

## 环境变量

在 `.env` 文件中设置以下环境变量：

```bash
# OAuth2 密钥
OAUTH2_PRIVATE_KEY=storage/oauth2/private.key
OAUTH2_PUBLIC_KEY=storage/oauth2/public.key
OAUTH2_PRIVATE_KEY_PASSPHRASE=
OAUTH2_ENCRYPTION_KEY=your-encryption-key-here

# 可选
OAUTH2_ENCRYPTION_KEY_TYPE=plain
```

## 可用命令

| 命令 | 描述 |
|---------|-------------|
| `oauth2:clear-expired-tokens` | 清除过期的访问/刷新令牌 |
| `oauth2:create-client` | 创建新的 OAuth2 客户端 |
| `oauth2:delete-client` | 删除 OAuth2 客户端 |
| `oauth2:generate-keypair` | 生成私钥/公钥对 |
| `oauth2:list-clients` | 列出所有 OAuth2 客户端 |
| `oauth2:update-client` | 更新 OAuth2 客户端 |

### 创建客户端

创建授权码授权的客户端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的网页应用" \
    --redirect-uri="https://myapp.com/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

创建密码授权的客户端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的移动应用" \
    --grant-type="password" \
    --grant-type="refresh_token"
```

创建客户端凭证授权的客户端：

```bash
php bin/hyperf.php oauth2:create-client \
    --name="我的API服务" \
    --grant-type="client_credentials"
```

## API 端点

### 授权端点

`GET /oauth/authorize`

用于授权码授权流程。参数：
- `response_type`: 必须是 `code`
- `client_id`: 客户端 ID
- `redirect_uri`: 必须与注册的回调 URI 匹配
- `scope`: 空格分隔的作用域列表
- `state`: CSRF 保护令牌
- `code_challenge`: PKCE 代码挑战
- `code_challenge_method`: PKCE 方法（通常是 `S256`）

### 令牌端点

`POST /oauth/token`

用于交换授权码获取访问令牌或使用其他授权类型。

### 受保护资源

使用 `ResourceServerMiddleware` 保护路由：

```php
use FriendsOfHyperf\Oauth2\Server\Middleware\ResourceServerMiddleware;

Router::addGroup('/api', function () {
    Router::get('user', [UserController::class, 'index']);
    Router::post('posts', [PostController::class, 'store']);
})->add(ResourceServerMiddleware::class);
```

## 授权类型

### 1. 客户端凭证授权

用于服务器到服务器认证：

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

### 2. 密码授权

用于可信应用（移动应用、SPA）：

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

### 3. 授权码授权

用于需要用户交互的网页应用：

**步骤1：重定向用户到授权端点**

```
https://your-server/oauth/authorize?response_type=code&client_id=your-client-id&redirect_uri=https://myapp.com/callback&scope=read&state=random-state&code_challenge=challenge&code_challenge_method=S256
```

**步骤2：交换授权码获取令牌**

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

### 4. 刷新令牌授权

获取新的访问令牌：

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

## 发起认证请求

在 Authorization 头中包含访问令牌：

```bash
curl -X GET http://your-server/api/user \
    -H "Authorization: Bearer your-access-token"
```

## 事件系统

组件会分发以下事件，您可以监听：

- `AuthorizationRequestResolveEvent`: 当授权请求需要用户批准时
- `UserResolveEvent`: 当为密码授权解析用户时
- `ScopeResolveEvent`: 当解析作用域时
- `TokenRequestResolveEvent`: 当处理令牌请求时

### 事件监听器示例

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
        // 验证用户凭据并返回用户ID
        if ($event->getUsername() === 'admin' && $event->getPassword() === 'secret') {
            $event->setUserId('1');
        }
    }
}
```

## 数据库表

该包创建以下表：

- `oauth_clients`: OAuth2 客户端
- `oauth_access_tokens`: 访问令牌
- `oauth_refresh_tokens`: 刷新令牌
- `oauth_auth_codes`: 授权码
- `oauth_personal_access_clients`: 个人访问客户端

## 自定义

### 自定义用户提供程序

通过监听 `UserResolveEvent` 实现您自己的用户解析逻辑。

### 自定义作用域管理

监听 `ScopeResolveEvent` 以实现自定义作用域逻辑。

### 自定义令牌存储

扩展存储库类以实现自定义存储后端。

## 安全最佳实践

1. 在生产环境中始终使用 HTTPS
2. 使用适当的文件权限安全存储私钥
3. 使用强加密密钥
4. 为授权流程实施适当的 CSRF 保护
5. 严格验证回调 URI
6. 使用短生命周期的访问令牌和刷新令牌
7. 在令牌端点上实施速率限制
8. 记录和监控令牌使用情况

## 测试

在开发过程中，您可以使用内置命令测试 OAuth2 流程：

```bash
# 创建测试客户端
php bin/hyperf.php oauth2:create-client \
    --name="测试客户端" \
    --redirect-uri="http://localhost:3000/callback" \
    --grant-type="authorization_code" \
    --grant-type="password" \
    --grant-type="refresh_token"

# 列出所有客户端
php bin/hyperf.php oauth2:list-clients

# 清除过期令牌
php bin/hyperf.php oauth2:clear-expired-tokens
```

## 错误处理

常见错误响应：

- `invalid_client`: 客户端认证失败
- `invalid_grant`: 无效的授权许可
- `invalid_request`: 缺少必需参数
- `invalid_scope`: 请求的作用域无效
- `unsupported_grant_type`: 不支持的授权类型
- `server_error`: 内部服务器错误

## 许可证

MIT