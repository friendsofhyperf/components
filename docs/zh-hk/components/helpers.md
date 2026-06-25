# Helpers

此組件為 Hyperf 提供常用輔助函數。

## 安裝

```shell
composer require friendsofhyperf/helpers
```

## 配置與自動加載

此包通過 Composer 自動加載函數文件。其配置提供器不會註冊或發佈任何配置，因此無需額外設置。

除 `call` 定義在 `FriendsOfHyperf\Helpers\Command` 命名空間外，其他函數均定義在
`FriendsOfHyperf\Helpers` 命名空間。

## 函數參考

| 函數 | 簽名與行為 |
| --- | --- |
| `app` | `app(null\|string\|callable $abstract = null, array $parameters = [])`：從容器解析服務；將可調用值轉換為 `Closure`。 |
| `base_path` | `base_path(string $path = ''): string`：返回 `BASE_PATH`，可追加路徑。 |
| `blank` / `filled` | 判斷值是否為空或非空。模型、數字和布爾值不視為空。 |
| `cache` | `cache(...$arguments)`：無參數時返回緩存服務；字符串參數用於讀取；數組參數用於設置其中第一組鍵值。 |
| `cookie` | 創建 `Cookie`；未傳名稱時返回 `CookieJarInterface` 服務。非零有效期以分鐘為單位。 |
| `class_namespace` | `class_namespace(object\|string $class): string`：返回類的命名空間。 |
| `di` | `di(?string $abstract = null, array $parameters = [])`：解析或創建服務。無容器時直接實例化類；此時不傳抽象名稱會拋出異常。 |
| `enum_value` | 返回有值枚舉的值、純枚舉的名稱或原值。空的非字符串值使用可選默認值。 |
| `event` | `event(object $event)`：分發事件並返回分發器的結果。 |
| `fluent` | `fluent(object\|array $value): Fluent`：創建 `Fluent` 對象。 |
| `get_client_ip` | 返回 `x-real-ip` 請求頭；不存在時返回請求的 `remote_addr`。 |
| `info` | `info(string\|Stringable $message, array $context = [], bool $backtrace = false)`：寫入 info 日誌；可附加 `backtrace` 上下文值。 |
| `literal` | 僅有一個位置參數時原樣返回；使用命名參數時創建對象。 |
| `logger` | 未傳消息時返回默認日誌記錄器；否則寫入 debug 日誌，並可附加調用棧。 |
| `logs` | `logs(string $name = 'hyperf', ?string $channel = null): LoggerInterface`：從 `LoggerFactory` 獲取日誌記錄器。 |
| `microseconds` / `milliseconds` / `months` / `weeks` | 創建指定單位的 `CarbonInterval`。 |
| `object_get` | 使用點號讀取嵌套對象屬性；鍵為空時返回對象，屬性不存在時求值並返回默認值。 |
| `preg_replace_array` | 使用替換數組中的值依次替換每個正則匹配項。 |
| `request` | 未傳鍵時返回請求；支持字符串鍵、鍵數組和可選默認值。 |
| `resolve` | `resolve(string\|callable $abstract, array $parameters = [])`：通過 `di` 解析服務，或將可調用值轉換為 `Closure`。 |
| `response` | 無參數時返回響應服務；否則使用字符串或 JSON 數組內容和狀態碼創建響應，並接受響應頭數組。 |
| `rescue` | 執行回調；捕獲任意 `Throwable` 後返回備用值，可選異常處理器會接收該異常。 |
| `session` | 未傳鍵時返回會話；數組用於存儲值，字符串鍵用於讀取值。 |
| `throw_if` / `throw_unless` | 根據條件拋出異常實例、異常類或以消息創建的 `RuntimeException`；不拋出時返回條件值。 |
| `transform` | 僅在值非空時執行回調；否則返回或求值默認值。 |
| `validator` | 無參數時返回驗證器工廠；否則使用數據、規則、消息和自定義屬性創建驗證器。 |
| `when` | 根據求值後的表達式返回選中的值或默認值；選中值為 `Closure` 時執行它。 |
| `Command\call` | `call(string $command, array $arguments = []): int`：使用 `NullOutput` 運行控制枱命令並返回退出碼。 |

## 可選依賴

僅為應用實際使用的輔助函數或集成安裝可選 Hyperf 包。此包建議使用以下兼容的 `~3.2.0`
版本：

| 包 | 相關用途 |
| --- | --- |
| `hyperf/cache` | `cache` |
| `hyperf/di` | 基於容器的服務解析 |
| `hyperf/framework` | 運行時服務綁定和 `Command\call` |
| `hyperf/logger` | `info`、`logger` 和 `logs` |
| `hyperf/session` | `session` |
| `hyperf/validation` | `validator` |
| `hyperf/amqp`、`hyperf/async-queue`、`hyperf/kafka` | 包元數據為相應集成建議的依賴；此組件中的函數未直接引用它們。 |

## 示例

使用命名空間函數前先導入：

```php
use function FriendsOfHyperf\Helpers\blank;
use function FriendsOfHyperf\Helpers\literal;
use function FriendsOfHyperf\Helpers\object_get;
use function FriendsOfHyperf\Helpers\transform;

$profile = literal(name: 'Taylor', contact: (object) ['email' => 'taylor@example.com']);

object_get($profile, 'contact.email'); // taylor@example.com
blank('  '); // true
transform(5, fn (int $value) => $value * 2); // 10
```

`cache` 會根據參數選擇行為：

```php
use function FriendsOfHyperf\Helpers\cache;

$cache = cache();
$value = cache('key', 'default');
cache(['key' => 'value'], 60);
```

控制枱命令使用單獨的命名空間：

```php
use function FriendsOfHyperf\Helpers\Command\call;

$exitCode = call('foo:bar', ['argument' => 'value']);
```
