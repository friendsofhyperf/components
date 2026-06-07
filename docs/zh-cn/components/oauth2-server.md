# OAuth2 Server

使用 `league/oauth2-server` 为 Hyperf 构建 OAuth 2.0 授权服务器。

## 安装

```shell
composer require friendsofhyperf/oauth2-server
php bin/hyperf.php vendor:publish friendsofhyperf/oauth2-server
php bin/hyperf.php migrate
```

发布命令会创建 `config/autoload/oauth2-server.php`。在该文件中配置私钥、公钥、加密密钥、
令牌有效期、启用的授权类型和 scopes。

配置私钥和公钥路径后生成密钥对：

```shell
php bin/hyperf.php oauth2-server:generate-keypair
```

## 配置

常用配置项包括：

| 配置项 | 说明 |
| --- | --- |
| `authorization_server.private_key` | 用于签发令牌的私钥路径。 |
| `authorization_server.private_key_passphrase` | 可选的私钥密码。 |
| `authorization_server.encryption_key` | 授权服务器使用的加密密钥。 |
| `authorization_server.encryption_key_type` | `plain` 或其他支持的 `EncryptionKeyType`。 |
| `authorization_server.access_token_ttl` | Access token 有效期，类型为 `DateInterval`。 |
| `authorization_server.refresh_token_ttl` | Refresh token 有效期，类型为 `DateInterval`。 |
| `authorization_server.persist_access_token` | 是否持久化已签发的 access token。 |
| `resource_server.public_key` | 用于验证令牌的公钥路径。 |
| `resource_server.jwt_leeway` | 可选的 JWT 时钟偏移宽限时间。 |
| `scopes.available` | 允许请求的 scopes。 |
| `scopes.default` | 未请求 scope 时默认分配的 scopes。 |

## 命令

| 命令 | 说明 |
| --- | --- |
| `oauth2-server:clear-expired-tokens` | 清理过期的 access token 和 refresh token。 |
| `oauth2-server:create-client` | 创建 OAuth2 客户端。 |
| `oauth2-server:delete-client` | 删除 OAuth2 客户端。 |
| `oauth2-server:generate-keypair` | 生成私钥/公钥密钥对。 |
| `oauth2-server:list-clients` | 列出 OAuth2 客户端。 |
| `oauth2-server:update-client` | 更新 OAuth2 客户端。 |

按应用需要创建客户端：

```shell
php bin/hyperf.php oauth2-server:create-client "My App" \
    --redirect-uri="https://myapp.example/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

## Token Endpoint

组件提供授权服务器工厂。可以在自己的控制器或路由处理器中处理 token 请求：

```php
use FriendsOfHyperf\Oauth2\Server\Factory\AuthorizationServerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TokenController
{
    public function __construct(private AuthorizationServerFactory $factory)
    {
    }

    public function token(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->factory->build()->respondToAccessTokenRequest($request, $response);
    }
}
```

## 保护资源

在需要保护的路由上使用 `ResourceServerMiddleware`：

```php
use FriendsOfHyperf\Oauth2\Server\Middleware\ResourceServerMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/api', function () {
    Router::get('user', [UserController::class, 'index']);
}, [
    'middleware' => [ResourceServerMiddleware::class],
]);
```

如果需要直接验证请求，可以通过 `ResourceServerFactory` 构建 resource server 并调用
`validateAuthenticatedRequest()`。
