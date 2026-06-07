# Encryption

The encryption component provides authenticated encryption for values and strings in Hyperf.

## Installation

```shell
composer require friendsofhyperf/encryption
```

The package targets Hyperf 3.2. Install the optional `opis/closure` package only when encrypted
closures need to be signed:

```shell
composer require opis/closure
```

## Configuration

Publish the configuration file:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

This creates `config/autoload/encryption.php` with these settings:

| Configuration key | Environment variable | Default |
| --- | --- | --- |
| `encryption.key` | `APP_KEY` | A bundled example `base64:` key |
| `encryption.cipher` | `APP_CIPHER` | `AES-256-CBC` |

Replace the bundled example key before using the component. A `base64:` prefix tells the component
to Base64-decode the remaining value before validating its length. Generate a new key for the
default cipher with:

```shell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Supported ciphers and raw key lengths are:

| Cipher | Key length |
| --- | --- |
| `AES-128-CBC` | 16 bytes |
| `AES-256-CBC` | 32 bytes |
| `AES-128-GCM` | 16 bytes |
| `AES-256-GCM` | 32 bytes |

Cipher names are case-insensitive. A missing key throws
`FriendsOfHyperf\Encryption\Exception\MissingKeyException`; an unsupported cipher or incorrect key
length throws `RuntimeException`.

## Helper Functions

The package autoloads namespaced `encrypt()` and `decrypt()` functions. Import them before use:

```php
use function FriendsOfHyperf\Encryption\decrypt;
use function FriendsOfHyperf\Encryption\encrypt;

$payload = encrypt(['id' => 1]);
$value = decrypt($payload);
```

`encrypt(mixed $value, bool $serialize = true)` serializes values by default, and
`decrypt(string $payload, bool $unserialize = true)` reverses that behavior. Pass `false` to both
second arguments when handling a raw string without serialization.

## Encrypter API

The container binds `FriendsOfHyperf\Encryption\Encrypter`,
`FriendsOfHyperf\Encryption\Contract\Encrypter`, and
`FriendsOfHyperf\Encryption\Contract\StringEncrypter` to the configured encrypter.

```php
use FriendsOfHyperf\Encryption\Contract\StringEncrypter;

class TokenService
{
    public function __construct(private StringEncrypter $encrypter)
    {
    }

    public function encrypt(string $token): string
    {
        return $this->encrypter->encryptString($token);
    }
}
```

The concrete `Encrypter` also exposes `encrypt()`, `decrypt()`, `getKey()`, `getAllKeys()`,
`getPreviousKeys()`, `previousKeys()`, and the static `supported()` and `generateKey()` methods.

## Key Rotation

Configure previous raw keys on the concrete encrypter to decrypt existing payloads after rotating
the current key. New payloads always use the current key.

```php
use FriendsOfHyperf\Encryption\Encrypter;

$encrypter->previousKeys([
    base64_decode('PREVIOUS_BASE64_KEY'),
]);
```

Every previous key must have the correct length for the current cipher.

## Failures and Optional Closure Signing

Encryption failures throw `FriendsOfHyperf\Encryption\Contract\EncryptException`. Invalid,
tampered, or undecryptable payloads throw
`FriendsOfHyperf\Encryption\Contract\DecryptException`.

When `opis/closure` is installed and `encryption.key` is configured, the boot listener registers the
same parsed key with `Opis\Closure\SerializableClosure` for closure signing. The component does not
require `opis/closure` for normal value or string encryption.
