# Async Queue Closure Job（异步队列闭包任务）

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/async-queue-closure-job)](https://packagist.org/packages/friendsofhyperf/async-queue-closure-job)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/async-queue-closure-job)](https://packagist.org/packages/friendsofhyperf/async-queue-closure-job)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/async-queue-closure-job)](https://github.com/friendsofhyperf/async-queue-closure-job)

Hyperf 的异步队列闭包任务组件。支持将闭包作为后台任务执行，完整支持依赖注入和流式配置。

## 安装

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## 基础用法

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 简单的闭包分发
dispatch(function () {
    // 你的任务逻辑
    var_dump('Hello from closure job!');
});

// 设置最大尝试次数（重试限制）
dispatch(function () {
    // 你的任务逻辑
})->setMaxAttempts(3);
```

## 高级用法

### 流式 API 配置

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 链式配置多个选项
dispatch(function () {
    // 你的任务逻辑
})
    ->onConnection('high-priority')  // 指定连接
    ->delay(60)                      // 延迟 60 秒执行
    ->setMaxAttempts(5);             // 最多重试 5 次
```

### 条件执行

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$condition = true;

// 仅当条件为 true 时执行
dispatch(function () {
    // 你的任务逻辑
})
    ->when($condition, function ($dispatch) {
        $dispatch->onConnection('conditional-connection');
    });

// 仅当条件为 false 时执行
dispatch(function () {
    // 你的任务逻辑
})
    ->unless($condition, function ($dispatch) {
        $dispatch->delay(30);
    });
```

### 依赖注入

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 自动依赖注入
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('处理 ' . count($users) . ' 个用户');
    // 处理用户...
});

// 带自定义参数
dispatch(function (UserService $userService, int $userId) {
    $user = $userService->find($userId);
    // 处理用户...
})->setMaxAttempts(3);
```

## API 参考

### `dispatch(Closure $closure): PendingClosureDispatch`

主要的分发函数，用于创建闭包任务。

### `PendingClosureDispatch` 方法

- `onConnection(string $connection): static` - 设置连接名称
- `delay(int $delay): static` - 设置延迟执行时间（秒）
- `setMaxAttempts(int $maxAttempts): static` - 设置最大重试次数
- `when($condition, $callback): static` - 当条件为真时执行回调
- `unless($condition, $callback): static` - 当条件为假时执行回调

### 支持的闭包类型

- 无参数的简单闭包
- 带依赖注入的闭包
- 使用捕获变量（`use`）的闭包
- 带可空参数的闭包

## 测试

运行测试：

```shell
composer test:unit -- tests/AsyncQueueClosureJob
```

## 贡献

欢迎贡献！请随时提交 Pull Request。

## 联系方式

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## 许可证

[MIT](LICENSE)

---

## 为 Hyperf 社区用 ❤️ 制作
