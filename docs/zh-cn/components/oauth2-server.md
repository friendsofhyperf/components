# OAuth2 服务器

基于 [league/oauth2-server](https://oauth2.thephpleague.com/) 的 Hyperf 框架完整 OAuth2 服务器实现。

## 功能特性

- 完整的 OAuth2 服务器实现，支持：
  - 客户端凭证授权 (Client Credentials Grant)
  - 密码授权 (Password Grant)
  - 刷新令牌授权 (Refresh Token Grant)
  - 授权码授权 (Authorization Code Grant，支持 PKCE)
  - 设备码授权 (Device Code Grant)
  - 隐式授权 (Implicit Grant)
- 内置客户端管理命令
- 多种存储后端 (Eloquent ORM)
- 可自定义的令牌生命周期
- 作用域管理
- 事件驱动架构
- 工厂模式实现
- 类型安全的值对象和枚举
- 完整的错误处理和日志记录

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

### 1. 客户端凭证授权 (Client Credentials Grant)

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

**适用场景：**
- 微服务间通信
- API 密钥认证
- 系统集成

### 2. 密码授权 (Password Grant)

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

**适用场景：**
- 移动应用
- 单页应用 (SPA)
- 可信的第三方应用

**注意：** 此授权方式需要高度信任客户端，谨慎使用。

### 3. 授权码授权 (Authorization Code Grant)

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

**PKCE 支持：**
- `code_challenge`: 生成的代码挑战
- `code_challenge_method`: S256 或 plain
- `code_verifier`: 用于验证的原始代码

**适用场景：**
- 网页应用
- 需要用户授权的应用
- 安全要求高的场景

### 4. 刷新令牌授权 (Refresh Token Grant)

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

**适用场景：**
- 延长用户会话
- 避免频繁重新登录
- 移动应用后台服务

### 5. 设备码授权 (Device Code Grant)

用于无输入设备（如智能电视、IoT设备）：

**步骤1：请求设备码**

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "device_code",
        "client_id": "your-client-id",
        "scope": "read"
    }'
```

**步骤2：用户在其他设备上授权**

用户需要在手机或电脑上访问显示的授权URL完成授权。

**步骤3：轮询令牌**

```bash
curl -X POST http://your-server/oauth/token \
    -H "Content-Type: application/json" \
    -d '{
        "grant_type": "device_code",
        "client_id": "your-client-id",
        "device_code": "device-code-from-step1"
    }'
```

**适用场景：**
- 智能电视应用
- IoT 设备
- 无输入设备的应用

### 6. 隐式授权 (Implicit Grant)

已不推荐使用，但在某些旧系统中仍可能遇到：

```
https://your-server/oauth/authorize?response_type=token&client_id=your-client-id&redirect_uri=https://myapp.com/callback&scope=read&state=random-state
```

**注意：** 此授权方式已被 OAuth 2.1 弃用，建议使用授权码授权 + PKCE。

## 工厂模式实现

组件使用工厂模式创建服务器实例，提供了更好的灵活性和可测试性：

### 授权服务器工厂

```php
use FriendsOfHyperf\Oauth2\Server\Factory\AuthorizationServerFactory;
use Hyperf\Di\Annotation\Inject;

class YourController
{
    #[Inject]
    private AuthorizationServerFactory $authorizationServerFactory;

    public function handleAuthorization()
    {
        // 构建授权服务器
        $authorizationServer = $this->authorizationServerFactory->build();
        
        // 使用授权服务器处理请求
        // ...
    }
}
```

### 资源服务器工厂

```php
use FriendsOfHyperf\Oauth2\Server\Factory\ResourceServerFactory;
use Hyperf\Di\Annotation\Inject;

class YourController
{
    #[Inject]
    private ResourceServerFactory $resourceServerFactory;

    public function getProtectedData()
    {
        // 构建资源服务器
        $resourceServer = $this->resourceServerFactory->build();
        
        // 验证访问令牌
        $accessToken = $resourceServer->validateAuthenticatedRequest($request);
        
        // 获取令牌信息
        $tokenId = $accessToken->getAttribute('oauth_access_token_id');
        $userId = $accessToken->getAttribute('oauth_user_id');
        
        // 返回受保护的数据
        return ['user_id' => $userId];
    }
}
```

### 配置工厂

```php
use FriendsOfHyperf\Oauth2\Server\Factory\ConfigFactory;
use Hyperf\Di\Annotation\Inject;

class YourController
{
    #[Inject]
    private ConfigFactory $configFactory;

    public function getConfig()
    {
        // 获取 OAuth2 配置
        $config = $this->configFactory->create();
        
        // 访问配置项
        $accessTokenTtl = $config->get('authorization_server.access_token_ttl');
        $encryptionKey = $config->get('authorization_server.encryption_key');
        
        return $config;
    }
}
```

## 发起认证请求

在 Authorization 头中包含访问令牌：

```bash
curl -X GET http://your-server/api/user \
    -H "Authorization: Bearer your-access-token"
```

## 事件系统

组件提供了完整的事件系统，允许您自定义 OAuth2 流程的各个方面：

### 可用事件

| 事件类 | 描述 | 使用场景 |
|--------|------|----------|
| `AuthorizationRequestResolveEvent` | 当授权请求需要用户批准时触发 | 实现自定义授权逻辑、显示授权页面 |
| `UserResolveEvent` | 当为密码授权解析用户时触发 | 实现自定义用户认证逻辑 |
| `ScopeResolveEvent` | 当解析作用域时触发 | 实现自定义作用域验证和过滤 |
| `TokenRequestResolveEvent` | 当处理令牌请求时触发 | 记录令牌发放、添加自定义响应头 |
| `PreSaveClientEvent` | 在保存客户端之前触发 | 验证客户端数据、添加默认值 |

### 事件监听器示例

#### 1. 自定义用户认证

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

        // 验证用户凭据
        $user = $this->userService->authenticate($event->getUsername(), $event->getPassword());
        
        if ($user) {
            // 设置用户实体
            $userEntity = new UserInterface();
            $userEntity->setIdentifier($user->id);
            $event->setUser($userEntity);
        }
    }
}
```

#### 2. 自定义授权处理

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

        // 检查客户端是否被允许访问请求的作用域
        if (!$this->isScopeAllowed($event->getClient(), $event->getScopes())) {
            // 返回自定义错误响应
            $response = $this->createErrorResponse('invalid_scope', 'Requested scope is not allowed');
            $event->setResponse($response);
            return;
        }

        // 自动批准可信客户端的请求
        if ($this->isTrustedClient($event->getClient())) {
            $event->resolveAuthorization(AuthorizationRequestResolveEvent::AUTHORIZATION_APPROVED);
        }
    }

    private function isScopeAllowed(ClientInterface $client, array $scopes): bool
    {
        // 实现作用域验证逻辑
        return true;
    }

    private function isTrustedClient(ClientInterface $client): bool
    {
        // 实现可信客户端检查逻辑
        return $client->getName() === 'Trusted App';
    }
}
```

#### 3. 自定义作用域解析

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

        // 根据用户角色动态调整作用域
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
        // 实现用户作用域获取逻辑
        return ['read', 'write'];
    }
}
```

#### 4. 令牌请求记录

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

        // 记录令牌发放
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

### 自定义客户端验证

通过监听 `PreSaveClientEvent` 实现客户端数据验证和默认值设置：

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
        
        // 设置默认作用域
        if (empty($client->getScopes())) {
            $client->setScopes(new Scope('read'));
        }
        
        // 验证重定向URI
        foreach ($client->getRedirectUris() as $uri) {
            if (!$this->isValidRedirectUri($uri)) {
                throw new \InvalidArgumentException('Invalid redirect URI');
            }
        }
        
        // 设置默认授权类型
        if (empty($client->getGrants())) {
            $client->setGrants(new Grant('client_credentials'));
        }
    }

    private function isValidRedirectUri(RedirectUri $uri): bool
    {
        // 实现URI验证逻辑
        return str_starts_with((string) $uri, 'https://');
    }
}
```

### 自定义错误处理

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
        
        // 自定义错误响应格式
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
        // 实现错误代码映射
        return match($error) {
            'invalid_client' => 'AUTH_001',
            'invalid_grant' => 'AUTH_002',
            'invalid_scope' => 'AUTH_003',
            default => 'AUTH_999',
        };
    }

    private function generateRequestId(): string
    {
        // 生成请求ID
        return uniqid('oauth_', true);
    }
}
```

### 自定义令牌响应

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
            // 添加自定义字段
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
        // 获取用户信息
        return [
            'id' => $tokenData['user_id'] ?? null,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
    }

    private function getPermissions(string $scope): array
    {
        // 根据作用域获取权限
        $permissions = [];
        $scopes = explode(' ', $scope);
        
        foreach ($scopes as $scope) {
            $permissions = array_merge($permissions, $this->scopeToPermissions($scope));
        }
        
        return array_unique($permissions);
    }

    private function scopeToPermissions(string $scope): array
    {
        // 作用域到权限的映射
        return match($scope) {
            'read' => ['user:read', 'post:read'],
            'write' => ['user:write', 'post:write'],
            'admin' => ['*'],
            default => [],
        };
    }
}
```

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