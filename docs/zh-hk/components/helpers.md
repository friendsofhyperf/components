# Helpers

此元件為 Hyperf 提供常用輔助函數。

## 安裝

```shell
composer require friendsofhyperf/helpers
```

## 配置與自動載入

此套件透過 Composer 自動載入函數檔案。其配置提供器不會註冊或發佈任何配置，因此無需額外設定。

除 `call` 定義在 `FriendsOfHyperf\Helpers\Command` 命名空間外，其他函數均定義在
`FriendsOfHyperf\Helpers` 命名空間。

## 函數參考

| 函數 | 簽名與行為 |
| --- | --- |
| `app` | `app(null\|string\|callable $abstract = null, array $parameters = [])`：從容器解析服務；將可調用值轉換為 `Closure`。 |
| `base_path` | `base_path(string $path = ''): string`：返回 `BASE_PATH`，可附加路徑。 |
| `blank` / `filled` | 判斷值是否為空或非空。模型、數字和布林值不視為空。 |
| `cache` | `cache(...$arguments)`：無參數時返回快取服務；字串參數用於讀取；陣列參數用於設定其中第一組鍵值。 |
| `cookie` | 建立 `Cookie`；未傳名稱時返回 `CookieJarInterface` 服務。非零有效期以分鐘為單位。 |
| `class_namespace` | `class_namespace(object\|string $class): string`：返回類別的命名空間。 |
| `di` | `di(?string $abstract = null, array $parameters = [])`：解析或建立服務。無容器時直接實例化類別；此時未傳抽象名稱會拋出例外。 |
| `enum_value` | 返回有值列舉的值、純列舉的名稱或原值。空的非字串值使用可選預設值。 |
| `event` | `event(object $event)`：分派事件並返回分派器的結果。 |
| `fluent` | `fluent(object\|array $value): Fluent`：建立 `Fluent` 物件。 |
| `get_client_ip` | 返回 `x-real-ip` 請求標頭；不存在時返回請求的 `remote_addr`。 |
| `info` | `info(string\|Stringable $message, array $context = [], bool $backtrace = false)`：寫入 info 日誌；可附加 `backtrace` 上下文值。 |
| `literal` | 僅有一個位置參數時原樣返回；使用具名參數時建立物件。 |
| `logger` | 未傳訊息時返回預設日誌記錄器；否則寫入 debug 日誌，並可附加調用堆疊。 |
| `logs` | `logs(string $name = 'hyperf', ?string $channel = null): LoggerInterface`：從 `LoggerFactory` 取得日誌記錄器。 |
| `microseconds` / `milliseconds` / `months` / `weeks` | 建立指定單位的 `CarbonInterval`。 |
| `object_get` | 使用點號讀取巢狀物件屬性；鍵為空時返回物件，屬性不存在時求值並返回預設值。 |
| `preg_replace_array` | 使用替換陣列中的值依次替換每個正則匹配項。 |
| `request` | 未傳鍵時返回請求；支援字串鍵、鍵陣列和可選預設值。 |
| `resolve` | `resolve(string\|callable $abstract, array $parameters = [])`：透過 `di` 解析服務，或將可調用值轉換為 `Closure`。 |
| `response` | 無參數時返回回應服務；否則使用字串或 JSON 陣列內容和狀態碼建立回應，並接受標頭陣列。 |
| `rescue` | 執行回調；捕獲任意 `Throwable` 後返回備用值，可選例外處理器會接收該例外。 |
| `session` | 未傳鍵時返回工作階段；陣列用於儲存值，字串鍵用於讀取值。 |
| `throw_if` / `throw_unless` | 根據條件拋出例外實例、例外類別或以訊息建立的 `RuntimeException`；不拋出時返回條件值。 |
| `transform` | 僅在值非空時執行回調；否則返回或求值預設值。 |
| `validator` | 無參數時返回驗證器工廠；否則使用資料、規則、訊息和自訂屬性建立驗證器。 |
| `when` | 根據求值後的表達式返回選中的值或預設值；選中值為 `Closure` 時執行它。 |
| `Command\call` | `call(string $command, array $arguments = []): int`：使用 `NullOutput` 執行控制台命令並返回退出碼。 |

## 可選依賴

僅為應用程式實際使用的輔助函數或整合安裝可選 Hyperf 套件。此套件建議使用以下兼容的
`~3.2.0` 版本：

| 套件 | 相關用途 |
| --- | --- |
| `hyperf/cache` | `cache` |
| `hyperf/di` | 基於容器的服務解析 |
| `hyperf/framework` | 執行階段服務綁定和 `Command\call` |
| `hyperf/logger` | `info`、`logger` 和 `logs` |
| `hyperf/session` | `session` |
| `hyperf/validation` | `validator` |
| `hyperf/amqp`、`hyperf/async-queue`、`hyperf/kafka` | 套件元數據為相應整合建議的依賴；此元件中的函數未直接引用它們。 |

## 範例

使用命名空間函數前先匯入：

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

控制台命令使用單獨的命名空間：

```php
use function FriendsOfHyperf\Helpers\Command\call;

$exitCode = call('foo:bar', ['argument' => 'value']);
```
