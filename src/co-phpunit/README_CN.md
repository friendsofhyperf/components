# Co-PHPUnit

[English](README.md)

Co-PHPUnit 让 PHPUnit 测试在 Swoole 协程中运行，适用于测试依赖协程上下文的 Hyperf
组件。

## 安装

将组件安装为开发依赖：

```bash
composer require friendsofhyperf/co-phpunit --dev
```

组件依赖 `hyperf/coordinator` `~3.2.0`，并支持 PHPUnit 10、11 和 12。它没有将 Swoole
扩展声明为 Composer 依赖；未加载该扩展时，测试会回退到 PHPUnit 的普通执行方式。

## 使用

### 在协程中运行测试

在测试类或共享的测试基类中使用 `RunTestsInCoroutine`：

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

安装组件后，不需要额外配置 PHPUnit 或 Composer 自动加载。

### 退出协程执行

将 `NonCoroutine` 应用于单个测试方法，可以让该方法在不创建协程的情况下运行：

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

也可以将 `#[NonCoroutine]` 应用于测试类，让该类中的所有测试方法退出协程执行。该属性可用于
类和方法，且没有构造参数。

组件的公开 API 包括：

- `FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine`；
- `FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine`。

## 执行行为

`RunTestsInCoroutine` 会重写 PHPUnit 的 `runBare()` 方法。

每个测试开始前，遇到以下任一情况时，它不会创建协程，而是以普通方式运行测试：

- 未加载 Swoole 扩展；
- 当前已经位于 Swoole 协程中；
- 测试类带有 `#[NonCoroutine]`；或
- 当前测试方法带有 `#[NonCoroutine]`。

否则，它会调用 `Swoole\Coroutine\run()`，并在协程内执行 PHPUnit 父类的 `runBare()`。
异常会先被捕获，并在协程退出后重新抛出。在 `finally` 块中，该 trait 会清除所有 Swoole
定时器，并恢复 Hyperf 的 `WORKER_EXIT` 协调器。仅当该 trait 创建协程时，才会执行这些
清理操作。

`#[NonCoroutine]` 只会阻止该 trait 创建协程。如果测试已经在协程内运行，该属性不会让测试
退出现有协程。

## PHPUnit 补丁

组件通过 Composer 的 `autoload.files` 注册 `phpunit-patch.php`。加载 Composer 自动加载
文件时，该补丁会找到 PHPUnit 的 `TestCase` 源文件；如果 `TestCase::runBare()` 带有
`final` 关键字，则将其移除，以便 trait 重写该方法。

该补丁会直接写入已安装的 PHPUnit 源文件。需要应用补丁时，请确保 Composer 的 vendor
目录可写。
