# Encryption

加密元件為 Hyperf 中的值和字串提供帶驗證的加密。

## 安裝

```shell
composer require friendsofhyperf/encryption
```

此套件面向 Hyperf 3.2。僅在需要為加密閉包簽章時安裝選用的 `opis/closure` 套件：

```shell
composer require opis/closure
```

## 設定

釋出設定檔：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/encryption
```

此命令會建立 `config/autoload/encryption.php`，其中包含以下設定：

| 設定鍵 | 環境變數 | 預設值 |
| --- | --- | --- |
| `encryption.key` | `APP_KEY` | 內建的範例 `base64:` 金鑰 |
| `encryption.cipher` | `APP_CIPHER` | `AES-256-CBC` |

使用元件前請替換內建範例金鑰。`base64:` 前綴表示元件會先對其後的內容進行 Base64 解碼，
再驗證金鑰長度。可使用以下命令為預設 cipher 產生新金鑰：

```shell
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

支援的 cipher 及原始金鑰長度如下：

| Cipher | 金鑰長度 |
| --- | --- |
| `AES-128-CBC` | 16 位元組 |
| `AES-256-CBC` | 32 位元組 |
| `AES-128-GCM` | 16 位元組 |
| `AES-256-GCM` | 32 位元組 |

Cipher 名稱不區分大小寫。缺少金鑰時會擲出
`FriendsOfHyperf\Encryption\Exception\MissingKeyException`；不支援的 cipher 或錯誤的金鑰長度會擲出
`RuntimeException`。

## 輔助函式

此套件會自動載入帶命名空間的 `encrypt()` 和 `decrypt()` 函式。使用前請先匯入：

```php
use function FriendsOfHyperf\Encryption\decrypt;
use function FriendsOfHyperf\Encryption\encrypt;

$payload = encrypt(['id' => 1]);
$value = decrypt($payload);
```

`encrypt(mixed $value, bool $serialize = true)` 預設會序列化值，
`decrypt(string $payload, bool $unserialize = true)` 會執行對應的反向操作。處理不需要序列化的原始字串時，
請將兩個函式的第二個參數都設為 `false`。

## Encrypter API

容器會將 `FriendsOfHyperf\Encryption\Encrypter`、
`FriendsOfHyperf\Encryption\Contract\Encrypter` 和
`FriendsOfHyperf\Encryption\Contract\StringEncrypter` 綁定到已設定的加密器。

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

## 金鑰輪替

輪替目前金鑰後，可在具體加密器上設定舊的原始金鑰，以解密已有 payload。新 payload 始終使用目前金鑰。

```php
use FriendsOfHyperf\Encryption\Encrypter;

$encrypter->previousKeys([
    base64_decode('PREVIOUS_BASE64_KEY'),
]);
```

每個舊金鑰的長度都必須符合目前 cipher 的要求。

## 失敗與選用閉包簽章

加密失敗會擲出 `FriendsOfHyperf\Encryption\Contract\EncryptException`。無效、遭竄改或無法解密的
payload 會擲出 `FriendsOfHyperf\Encryption\Contract\DecryptException`。

安裝 `opis/closure` 且已設定 `encryption.key` 時，啟動監聽器會向
`Opis\Closure\SerializableClosure` 註冊同一個解析後的金鑰，用於閉包簽章。正常的值或字串加密不依賴
`opis/closure`。
