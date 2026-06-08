# OAuth2 Server

Build an OAuth 2.0 authorization server for Hyperf with `league/oauth2-server`.

## Installation

```shell
composer require friendsofhyperf/oauth2-server
php bin/hyperf.php vendor:publish friendsofhyperf/oauth2-server
php bin/hyperf.php migrate
```

The publish command creates `config/autoload/oauth2-server.php`. Configure private and public
keys, the encryption key, token lifetimes, enabled grants, and scopes in that file.

Generate a key pair after configuring the private and public key paths:

```shell
php bin/hyperf.php oauth2-server:generate-keypair
```

## Configuration

Important configuration keys include:

| Key | Description |
| --- | --- |
| `authorization_server.private_key` | Private key path used to sign tokens. |
| `authorization_server.private_key_passphrase` | Optional private key passphrase. |
| `authorization_server.encryption_key` | Encryption key used by the authorization server. |
| `authorization_server.encryption_key_type` | `plain` or another supported `EncryptionKeyType`. |
| `authorization_server.access_token_ttl` | Access token lifetime as a `DateInterval`. |
| `authorization_server.refresh_token_ttl` | Refresh token lifetime as a `DateInterval`. |
| `authorization_server.persist_access_token` | Whether issued access tokens are persisted. |
| `resource_server.public_key` | Public key path used to validate tokens. |
| `resource_server.jwt_leeway` | Optional JWT clock-skew leeway. |
| `scopes.available` | Scopes that may be requested. |
| `scopes.default` | Scopes assigned when no scope is requested. |

## Commands

| Command | Description |
| --- | --- |
| `oauth2-server:clear-expired-tokens` | Remove expired access and refresh tokens. |
| `oauth2-server:create-client` | Create an OAuth2 client. |
| `oauth2-server:delete-client` | Delete an OAuth2 client. |
| `oauth2-server:generate-keypair` | Generate a private/public key pair. |
| `oauth2-server:list-clients` | List OAuth2 clients. |
| `oauth2-server:update-client` | Update an OAuth2 client. |

Create a client with the grants and redirect URIs your application needs:

```shell
php bin/hyperf.php oauth2-server:create-client "My App" \
    --redirect-uri="https://myapp.example/callback" \
    --grant-type="authorization_code" \
    --grant-type="refresh_token"
```

## Token Endpoint

The package provides an authorization server factory. Use it from your own controller or route
handler for token requests:

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

## Protecting Resources

Use `ResourceServerMiddleware` on protected routes:

```php
use FriendsOfHyperf\Oauth2\Server\Middleware\ResourceServerMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addGroup('/api', function () {
    Router::get('user', [UserController::class, 'index']);
}, [
    'middleware' => [ResourceServerMiddleware::class],
]);
```

If you need direct validation, build the resource server with `ResourceServerFactory` and call
`validateAuthenticatedRequest()`.
