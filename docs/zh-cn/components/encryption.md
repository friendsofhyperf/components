# Encryption

加密组件为 Hyperf 中的值和字符串提供带认证的加密。

## 安装

```shell
composer require friendsofhyperf/encryption
```

此包面向 Hyperf 3.2。仅在需要为加密闭包签名时安装可选的 `opis/closure` 包：

```shell
composer require opis/closure
```

## 配置

发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

此命令会创建 `config/autoload/encryption.php`，其中包含以下设置：

| 配置键 | 环境变量 | 默认值 |
| --- | --- | --- |
| `encryption.key` | `APP_KEY` | 内置的示例 `base64:` 密钥 |
| `encryption.cipher` | `APP_CIPHER` | `AES-256-CBC` |

使用组件前请替换内置示例密钥。`base64:` 前缀表示组件会先对其后的内容进行 Base64 解码，
再校验密钥长度。可使用以下命令为默认 cipher 生成新密钥：

```shell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

支持的 cipher 及原始密钥长度如下：

| Cipher | 密钥长度 |
| --- | --- |
| `AES-128-CBC` | 16 字节 |
| `AES-256-CBC` | 32 字节 |
| `AES-128-GCM` | 16 字节 |
| `AES-256-GCM` | 32 字节 |

Cipher 名称不区分大小写。缺少密钥时会抛出
`FriendsOfHyperf\Encryption\Exception\MissingKeyException`；不支持的 cipher 或错误的密钥长度会抛出
`RuntimeException`。

## 助手函数

此包会自动加载带命名空间的 `encrypt()` 和 `decrypt()` 函数。使用前请先导入：

```php
use function FriendsOfHyperf\Encryption\decrypt;
use function FriendsOfHyperf\Encryption\encrypt;

$payload = encrypt(['id' => 1]);
$value = decrypt($payload);
```

`encrypt(mixed $value, bool $serialize = true)` 默认会序列化值，
`decrypt(string $payload, bool $unserialize = true)` 会执行对应的反向操作。处理不需要序列化的原始字符串时，
请将两个函数的第二个参数都设为 `false`。

## Encrypter API

容器会将 `FriendsOfHyperf\Encryption\Encrypter`、
`FriendsOfHyperf\Encryption\Contract\Encrypter` 和
`FriendsOfHyperf\Encryption\Contract\StringEncrypter` 绑定到已配置的加密器。

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

具体类 `Encrypter` 还公开 `encrypt()`、`decrypt()`、`getKey()`、`getAllKeys()`、
`getPreviousKeys()`、`previousKeys()`，以及静态方法 `supported()` 和 `generateKey()`。

## 密钥轮换

轮换当前密钥后，可在具体加密器上配置旧的原始密钥，以解密已有 payload。新 payload 始终使用当前密钥。

```php
use FriendsOfHyperf\Encryption\Encrypter;

$encrypter->previousKeys([
    base64_decode('PREVIOUS_BASE64_KEY'),
]);
```

每个旧密钥的长度都必须符合当前 cipher 的要求。

## 失败与可选闭包签名

加密失败会抛出 `FriendsOfHyperf\Encryption\Contract\EncryptException`。无效、被篡改或无法解密的
payload 会抛出 `FriendsOfHyperf\Encryption\Contract\DecryptException`。

安装 `opis/closure` 且已配置 `encryption.key` 时，启动监听器会向
`Opis\Closure\SerializableClosure` 注册同一个解析后的密钥，用于闭包签名。正常的值或字符串加密不依赖
`opis/closure`。
