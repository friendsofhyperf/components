# OAuth2 Server

使用 `league/oauth2-server` 為 Hyperf 構建 OAuth 2.0 授權服務器。

## 安裝

```shell
composer require friendsofhyperf/oauth2-server
php bin/hyperf.php vendor:publish friendsofhyperf/oauth2-server
php bin/hyperf.php migrate
```

發佈命令會創建 `config/autoload/oauth2-server.php`。在該文件中配置私鑰、公鑰、加密密鑰、
令牌有效期、啓用的授權類型和 scopes。

配置私鑰和公鑰路徑後生成密鑰對：

```shell
php bin/hyperf.php oauth2-server:generate-keypair
```

## 配置

常用配置項包括：

| 配置項 | 説明 |
| --- | --- |
| `authorization_server.private_key` | 用於簽發令牌的私鑰路徑。 |
| `authorization_server.private_key_passphrase` | 可選的私鑰密碼。 |
| `authorization_server.encryption_key` | 授權服務器使用的加密密鑰。 |
| `authorization_server.encryption_key_type` | `plain` 或其他支持的 `EncryptionKeyType`。 |
| `authorization_server.access_token_ttl` | Access token 有效期，類型為 `DateInterval`。 |
| `authorization_server.refresh_token_ttl` | Refresh token 有效期，類型為 `DateInterval`。 |
| `authorization_server.persist_access_token` | 是否持久化已簽發的 access token。 |
| `resource_server.public_key` | 用於驗證令牌的公鑰路徑。 |
| `resource_server.jwt_leeway` | 可選的 JWT 時鐘偏移寬限時間。 |
| `scopes.available` | 允許請求的 scopes。 |
| `scopes.default` | 未請求 scope 時默認分配的 scopes。 |

## 命令

| 命令 | 説明 |
| --- | --- |
| `oauth2-server:clear-expired-tokens` | 清理過期的 access token 和 refresh token。 |
| `oauth2-server:create-client` | 創建 OAuth2 客户端。 |
| `oauth2-server:delete-client` | 刪除 OAuth2 客户端。 |
| `oauth2-server:generate-keypair` | 生成私鑰/公鑰密鑰對。 |
| `oauth2-server:list-clients` | 列出 OAuth2 客户端。 |
| `oauth2-server:update-client` | 更新 OAuth2 客户端。 |

按應用需要創建客户端：

```shell
php bin/hyperf.php oauth2-server:create-client "My App" \
    --redirect-uri="https://myapp.example/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

## Token Endpoint

組件提供授權服務器工廠。可以在自己的控制器或路由處理器中處理 token 請求：

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

## 保護資源

在需要保護的路由上使用 `ResourceServerMiddleware`：

```php
use FriendsOfHyperf\Oauth2\Server\Middleware\ResourceServerMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/api', function () {
    Router::get('user', [UserController::class, 'index']);
}, [
    'middleware' => [ResourceServerMiddleware::class],
]);
```

如果需要直接驗證請求，可以通過 `ResourceServerFactory` 構建 resource server 並調用
`validateAuthenticatedRequest()`。
