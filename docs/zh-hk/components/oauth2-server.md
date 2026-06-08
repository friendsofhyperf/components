# OAuth2 Server

使用 `league/oauth2-server` 為 Hyperf 建立 OAuth 2.0 授權伺服器。

## 安裝

```shell
composer require friendsofhyperf/oauth2-server
php bin/hyperf.php vendor:publish friendsofhyperf/oauth2-server
php bin/hyperf.php migrate
```

發佈命令會建立 `config/autoload/oauth2-server.php`。在該檔案中設定私鑰、公鑰、加密金鑰、
權杖有效期、啟用的授權類型和 scopes。

設定私鑰和公鑰路徑後產生密鑰對：

```shell
php bin/hyperf.php oauth2-server:generate-keypair
```

## 設定

常用設定項包括：

| 設定項 | 說明 |
| --- | --- |
| `authorization_server.private_key` | 用於簽發權杖的私鑰路徑。 |
| `authorization_server.private_key_passphrase` | 可選的私鑰密碼。 |
| `authorization_server.encryption_key` | 授權伺服器使用的加密金鑰。 |
| `authorization_server.encryption_key_type` | `plain` 或其他支援的 `EncryptionKeyType`。 |
| `authorization_server.access_token_ttl` | Access token 有效期，類型為 `DateInterval`。 |
| `authorization_server.refresh_token_ttl` | Refresh token 有效期，類型為 `DateInterval`。 |
| `authorization_server.persist_access_token` | 是否持久化已簽發的 access token。 |
| `resource_server.public_key` | 用於驗證權杖的公鑰路徑。 |
| `resource_server.jwt_leeway` | 可選的 JWT 時鐘偏移寬限時間。 |
| `scopes.available` | 允許請求的 scopes。 |
| `scopes.default` | 未請求 scope 時預設分配的 scopes。 |

## 命令

| 命令 | 說明 |
| --- | --- |
| `oauth2-server:clear-expired-tokens` | 清理過期的 access token 和 refresh token。 |
| `oauth2-server:create-client` | 建立 OAuth2 用戶端。 |
| `oauth2-server:delete-client` | 刪除 OAuth2 用戶端。 |
| `oauth2-server:generate-keypair` | 產生私鑰/公鑰密鑰對。 |
| `oauth2-server:list-clients` | 列出 OAuth2 用戶端。 |
| `oauth2-server:update-client` | 更新 OAuth2 用戶端。 |

按應用需要建立用戶端：

```shell
php bin/hyperf.php oauth2-server:create-client "My App" \
    --redirect-uri="https://myapp.example/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

## Token Endpoint

元件提供授權伺服器工廠。可以在自己的控制器或路由處理器中處理 token 請求：

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

如果需要直接驗證請求，可以透過 `ResourceServerFactory` 建立 resource server 並呼叫
`validateAuthenticatedRequest()`。
