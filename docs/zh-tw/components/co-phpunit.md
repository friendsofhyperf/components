# Co-PHPUnit

一個 PHPUnit 擴充套件，使測試能夠在 Swoole 協程中執行，專為測試 Hyperf 應用程式和其他基於 Swoole 的框架而設計。

## 安裝

```bash
composer require friendsofhyperf/co-phpunit --dev
```

## 為什麼需要 Co-PHPUnit？

在測試 Hyperf 應用程式時，許多元件依賴 Swoole 的協程上下文才能正常工作。在傳統的同步環境中執行測試可能會導致以下問題：

- 協程上下文不可用
- 定時器和事件迴圈無法正常工作
- 協調器模式失敗
- 資料庫連線池問題

Co-PHPUnit 透過在需要時自動將測試執行包裝在 Swoole 協程上下文中來解決這些問題。

## 使用

### 基本使用

只需在測試類中使用 `RunTestsInCoroutine` trait：

```php
<?php

namespace Your\Namespace\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;

class YourTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testSomething()
    {
        // 您的測試程式碼
        // 這將自動在 Swoole 協程中執行
    }
}
```

### 停用特定測試的協程

如果需要為特定測試類停用協程執行，請將 `$enableCoroutine` 屬性設定為 `false`：

```php
<?php

namespace Your\Namespace\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;

class YourTest extends TestCase
{
    use RunTestsInCoroutine;

    protected bool $enableCoroutine = false;

    public function testSomething()
    {
        // 此測試將在普通同步模式下執行
    }
}
```

## 工作原理

`RunTestsInCoroutine` trait 重寫了 PHPUnit 的 `runBare()` 方法以：

1. **檢查先決條件**：驗證 Swoole 擴充套件是否已載入且不在協程上下文中
2. **建立協程上下文**：將測試執行包裝在 `Swoole\Coroutine\run()` 中
3. **異常處理**：正確捕獲並重新丟擲協程內的異常
4. **清理**：測試完成時清除所有定時器並恢復協調器
5. **回退**：如果不滿足條件，則回退到正常測試執行

### PHPUnit 補丁

該包包含一個 `phpunit-patch.php` 檔案，自動從 PHPUnit 的 `TestCase::runBare()` 方法中移除 `final` 關鍵字，允許 trait 重寫它。此補丁在包自動載入時自動應用。

## 要求

- PHP >= 8.0
- PHPUnit >= 10.0
- Swoole 擴充套件（在協程模式下執行測試時）
- Hyperf >= 3.1（用於協調器功能）

## 配置

### Composer 自動載入

該包會自動在 composer.json 中註冊其自動載入檔案：

```json
{
    "autoload-dev": {
        "psr-4": {
            "Your\\Tests\\": "tests/"
        },
        "files": [
            "vendor/friendsofhyperf/co-phpunit/phpunit-patch.php"
        ]
    }
}
```

### PHPUnit 配置

不需要特殊的 PHPUnit 配置。該包與您現有的 `phpunit.xml` 配置無縫協作。

## 最佳實踐

1. **用於整合測試**：這對於與 Hyperf 的協程感知元件互動的整合測試特別有用
2. **選擇性啟用**：並非所有測試都需要在協程中執行。對於不需要協程上下文的單元測試，使用 `$enableCoroutine = false`
3. **測試隔離**：該包會自動清理測試之間的定時器和協調器狀態
4. **效能**：在協程中執行的測試可能具有略微不同的效能特徵

## 示例：測試 Hyperf 服務

```php
<?php

namespace App\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use Hyperf\Context\ApplicationContext;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testServiceWithCoroutineContext()
    {
        // 從容器獲取服務
        $service = ApplicationContext::getContainer()->get(YourService::class);

        // 測試使用協程上下文的方法
        $result = $service->asyncOperation();

        $this->assertNotNull($result);
    }

    public function testDatabaseConnection()
    {
        // 測試需要連線池的資料庫操作
        $result = Db::table('users')->first();

        $this->assertIsArray($result);
    }
}
```

## 故障排除

### 測試掛起或超時

如果測試掛起，請確保：
- 所有非同步操作都已正確等待
- 協程回撥中不存在無限迴圈
- 測試拆卸中清除了定時器

### "Call to a member function on null"

這通常表明協程上下文不可用。請確保：
- Swoole 擴充套件已安裝並啟用
- 包含了 `RunTestsInCoroutine` trait
- `$enableCoroutine` 設定為 `true`

### PHPUnit 版本相容性

該包支援 PHPUnit 10.x、11.x 和 12.x。請確保您的 PHPUnit 版本相容。
