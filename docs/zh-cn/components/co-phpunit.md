# Co-PHPUnit

一个 PHPUnit 扩展，使测试能够在 Swoole 协程中运行，专为测试 Hyperf 应用程序和其他基于 Swoole 的框架而设计。

## 安装

```bash
composer require friendsofhyperf/co-phpunit --dev
```

## 为什么需要 Co-PHPUnit？

在测试 Hyperf 应用程序时，许多组件依赖 Swoole 的协程上下文才能正常工作。在传统的同步环境中运行测试可能会导致以下问题：

- 协程上下文不可用
- 定时器和事件循环无法正常工作
- 协调器模式失败
- 数据库连接池问题

Co-PHPUnit 通过在需要时自动将测试执行包装在 Swoole 协程上下文中来解决这些问题。

## 使用

### 基本使用

只需在测试类中使用 `RunTestsInCoroutine` trait：

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
        // 您的测试代码
        // 这将自动在 Swoole 协程中运行
    }
}
```

### 禁用特定测试的协程

如果需要为特定测试类禁用协程执行，请将 `$enableCoroutine` 属性设置为 `false`：

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
        // 此测试将在普通同步模式下运行
    }
}
```

## 工作原理

`RunTestsInCoroutine` trait 重写了 PHPUnit 的 `runBare()` 方法以：

1. **检查先决条件**：验证 Swoole 扩展是否已加载且不在协程上下文中
2. **创建协程上下文**：将测试执行包装在 `Swoole\Coroutine\run()` 中
3. **异常处理**：正确捕获并重新抛出协程内的异常
4. **清理**：测试完成时清除所有定时器并恢复协调器
5. **回退**：如果不满足条件，则回退到正常测试执行

### PHPUnit 补丁

该包包含一个 `phpunit-patch.php` 文件，自动从 PHPUnit 的 `TestCase::runBare()` 方法中移除 `final` 关键字，允许 trait 重写它。此补丁在包自动加载时自动应用。

## 要求

- PHP >= 8.0
- PHPUnit >= 10.0
- Swoole 扩展（在协程模式下运行测试时）
- Hyperf >= 3.1（用于协调器功能）

## 配置

### Composer 自动加载

该包会自动在 composer.json 中注册其自动加载文件：

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

不需要特殊的 PHPUnit 配置。该包与您现有的 `phpunit.xml` 配置无缝协作。

## 最佳实践

1. **用于集成测试**：这对于与 Hyperf 的协程感知组件交互的集成测试特别有用
2. **选择性启用**：并非所有测试都需要在协程中运行。对于不需要协程上下文的单元测试，使用 `$enableCoroutine = false`
3. **测试隔离**：该包会自动清理测试之间的定时器和协调器状态
4. **性能**：在协程中运行的测试可能具有略微不同的性能特征

## 示例：测试 Hyperf 服务

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
        // 从容器获取服务
        $service = ApplicationContext::getContainer()->get(YourService::class);

        // 测试使用协程上下文的方法
        $result = $service->asyncOperation();

        $this->assertNotNull($result);
    }

    public function testDatabaseConnection()
    {
        // 测试需要连接池的数据库操作
        $result = Db::table('users')->first();

        $this->assertIsArray($result);
    }
}
```

## 故障排除

### 测试挂起或超时

如果测试挂起，请确保：
- 所有异步操作都已正确等待
- 协程回调中不存在无限循环
- 测试拆卸中清除了定时器

### "Call to a member function on null"

这通常表明协程上下文不可用。请确保：
- Swoole 扩展已安装并启用
- 包含了 `RunTestsInCoroutine` trait
- `$enableCoroutine` 设置为 `true`

### PHPUnit 版本兼容性

该包支持 PHPUnit 10.x、11.x 和 12.x。请确保您的 PHPUnit 版本兼容。
