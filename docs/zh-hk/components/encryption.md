# Encryption

加密組件為 Hyperf 中的值和字串提供帶認證的加密。

## 安裝

```shell
composer require friendsofhyperf/encryption
```

此套件面向 Hyperf 3.2。僅在需要為加密閉包簽名時安裝可選的 `opis/closure` 套件：

```shell
composer require opis/closure
```

## 配置

發佈配置檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

此命令會建立 `config/autoload/encryption.php`，其中包含以下設定：

| 配置鍵 | 環境變數 | 預設值 |
| --- | --- | --- |
| `encryption.key` | `APP_KEY` | 內置的範例 `base64:` 密鑰 |
| `encryption.cipher` | `APP_CIPHER` | `AES-256-CBC` |

使用組件前請替換內置範例密鑰。`base64:` 前綴表示組件會先對其後的內容進行 Base64 解碼，
再校驗密鑰長度。可使用以下命令為預設 cipher 產生新密鑰：

```shell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

支援的 cipher 及原始密鑰長度如下：

| Cipher | 密鑰長度 |
| --- | --- |
| `AES-128-CBC` | 16 位元組 |
| `AES-256-CBC` | 32 位元組 |
| `AES-128-GCM` | 16 位元組 |
| `AES-256-GCM` | 32 位元組 |

Cipher 名稱不區分大小寫。缺少密鑰時會拋出
`FriendsOfHyperf\Encryption\Exception\MissingKeyException`；不支援的 cipher 或錯誤的密鑰長度會拋出
`RuntimeException`。

## 輔助函數

此套件會自動載入帶命名空間的 `encrypt()` 和 `decrypt()` 函數。使用前請先匯入：

```php
use function FriendsOfHyperf\Encryption\decrypt;
use function FriendsOfHyperf\Encryption\encrypt;

$payload = encrypt(['id' => 1]);
$value = decrypt($payload);
```

`encrypt(mixed $value, bool $serialize = true)` 預設會序列化值，
`decrypt(string $payload, bool $unserialize = true)` 會執行對應的反向操作。處理不需要序列化的原始字串時，
請將兩個函數的第二個參數都設為 `false`。

## Encrypter API

容器會將 `FriendsOfHyperf\Encryption\Encrypter`、
`FriendsOfHyperf\Encryption\Contract\Encrypter` 和
`FriendsOfHyperf\Encryption\Contract\StringEncrypter` 綁定到已配置的加密器。

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

具體類別 `Encrypter` 還公開 `encrypt()`、`decrypt()`、`getKey()`、`getAllKeys()`、
`getPreviousKeys()`、`previousKeys()`，以及靜態方法 `supported()` 和 `generateKey()`。

## 密鑰輪換

輪換目前密鑰後，可在具體加密器上配置舊的原始密鑰，以解密已有 payload。新 payload 始終使用目前密鑰。

```php
use FriendsOfHyperf\Encryption\Encrypter;

$encrypter->previousKeys([
    base64_decode('PREVIOUS_BASE64_KEY'),
]);
```

每個舊密鑰的長度都必須符合目前 cipher 的要求。

## 失敗與可選閉包簽名

加密失敗會拋出 `FriendsOfHyperf\Encryption\Contract\EncryptException`。無效、被篡改或無法解密的
payload 會拋出 `FriendsOfHyperf\Encryption\Contract\DecryptException`。

安裝 `opis/closure` 且已配置 `encryption.key` 時，啟動監聽器會向
`Opis\Closure\SerializableClosure` 註冊同一個解析後的密鑰，用於閉包簽名。正常的值或字串加密不依賴
`opis/closure`。
