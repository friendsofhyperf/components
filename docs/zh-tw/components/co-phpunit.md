# Co-PHPUnit

Co-PHPUnit 讓 PHPUnit 測試在 Swoole 協程中執行，適用於測試依賴協程上下文的 Hyperf
元件。

## 安裝

將元件安裝為開發依賴：

```bash
composer require friendsofhyperf/co-phpunit --dev
```

元件依賴 `hyperf/coordinator` `~3.2.0`，並支援 PHPUnit 10、11 和 12。它沒有將 Swoole
擴充套件宣告為 Composer 依賴；未載入該擴充套件時，測試會回退到 PHPUnit 的普通執行方式。

## 使用

### 在協程中執行測試

在測試類或共享的測試基類中使用 `RunTestsInCoroutine`：

```php
<?php

namespace App\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class ServiceTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testRunsInCoroutine(): void
    {
        self::assertNotSame(-1, Coroutine::getCid());
    }
}
```

安裝元件後，不需要額外配置 PHPUnit 或 Composer 自動載入。

### 退出協程執行

將 `NonCoroutine` 應用於單個測試方法，可以讓該方法在不建立協程的情況下執行：

```php
<?php

namespace App\Tests;

use FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine;
use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class ServiceTest extends TestCase
{
    use RunTestsInCoroutine;

    #[NonCoroutine]
    public function testRunsWithoutCoroutine(): void
    {
        self::assertSame(-1, Coroutine::getCid());
    }
}
```

也可以將 `#[NonCoroutine]` 應用於測試類，讓該類中的所有測試方法退出協程執行。該屬性可用於
類和方法，且沒有構造引數。

元件的公開 API 包括：

- `FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine`；
- `FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine`。

## 執行行為

`RunTestsInCoroutine` 會重寫 PHPUnit 的 `runBare()` 方法。

每個測試開始前，遇到以下任一情況時，它不會建立協程，而是以普通方式執行測試：

- 未載入 Swoole 擴充套件；
- 當前已經位於 Swoole 協程中；
- 測試類帶有 `#[NonCoroutine]`；或
- 當前測試方法帶有 `#[NonCoroutine]`。

否則，它會呼叫 `Swoole\Coroutine\run()`，並在協程內執行 PHPUnit 父類的 `runBare()`。
異常會先被捕獲，並在協程退出後重新丟擲。在 `finally` 塊中，該 trait 會清除所有 Swoole
定時器，並恢復 Hyperf 的 `WORKER_EXIT` 協調器。僅當該 trait 建立協程時，才會執行這些
清理操作。

`#[NonCoroutine]` 只會阻止該 trait 建立協程。如果測試已經在協程內執行，該屬性不會讓測試
退出現有協程。

## PHPUnit 補丁

元件透過 Composer 的 `autoload.files` 註冊 `phpunit-patch.php`。載入 Composer 自動載入
檔案時，該補丁會找到 PHPUnit 的 `TestCase` 原始檔；如果 `TestCase::runBare()` 帶有
`final` 關鍵字，則將其移除，以便 trait 重寫該方法。

該補丁會直接寫入已安裝的 PHPUnit 原始檔。需要應用補丁時，請確保 Composer 的 vendor
目錄可寫。
