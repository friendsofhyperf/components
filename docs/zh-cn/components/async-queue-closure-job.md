# Async Queue Closure Job

## 简介

`friendsofhyperf/async-queue-closure-job` 是一个用于 Hyperf 的异步队列闭包任务组件。它允许你将闭包作为后台任务执行，完整支持依赖注入和流式配置，让异步任务的使用变得更加简单和优雅。

与传统的创建任务类的方式不同，该组件允许你直接使用闭包来定义任务逻辑，无需创建额外的类文件，使代码更加简洁。

## 安装

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## 基础用法

### 简单的闭包任务

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 分发一个简单的闭包任务
dispatch(function () {
    // 你的任务逻辑
    var_dump('Hello from closure job!');
});
```

### 设置最大尝试次数

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 设置最大尝试次数（重试限制）
dispatch(function () {
    // 你的任务逻辑
    // 如果失败，将重试最多 3 次
})->setMaxAttempts(3);
```

## 高级用法

### 流式 API 配置

通过链式调用的方式，你可以灵活地配置任务的各种选项：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 链式配置多个选项
dispatch(function () {
    // 你的任务逻辑
})
    ->onPool('high-priority')  // 指定队列连接
    ->delay(60)                      // 延迟 60 秒执行
    ->setMaxAttempts(5);             // 最多重试 5 次
```

### 指定队列连接

当你有多个队列连接时，可以指定任务使用的连接：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 使用指定的队列连接
dispatch(function () {
    // 高优先级任务逻辑
})->onPool('high-priority');

// 或者使用 onPool 方法（别名）
dispatch(function () {
    // 低优先级任务逻辑
})->onPool('low-priority');
```

### 延迟执行

你可以设置任务在一段时间后才开始执行：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 延迟 60 秒后执行
dispatch(function () {
    // 你的任务逻辑
})->delay(60);

// 延迟 5 分钟后执行
dispatch(function () {
    // 你的任务逻辑
})->delay(300);
```

### 条件执行

使用 `when` 和 `unless` 方法，可以根据条件动态配置任务：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$isUrgent = true;

// 仅当条件为 true 时执行回调
dispatch(function () {
    // 你的任务逻辑
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onPool('urgent');
    });

// 仅当条件为 false 时执行回调
dispatch(function () {
    // 你的任务逻辑
})
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(300);
    });

// 组合使用
dispatch(function () {
    // 你的任务逻辑
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onPool('urgent');
    })
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(60);
    });
```

### 依赖注入

闭包任务完整支持 Hyperf 的依赖注入功能，你可以在闭包参数中声明需要的依赖：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;
use App\Service\UserService;
use Psr\Log\LoggerInterface;

// 自动依赖注入
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('正在处理 ' . count($users) . ' 个用户');
    
    foreach ($users as $user) {
        // 处理用户...
    }
});
```

### 使用捕获变量

你可以通过 `use` 关键字在闭包中使用外部变量：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userId = 123;
$action = 'update';

// 使用捕获变量
dispatch(function (UserService $userService) use ($userId, $action) {
    $user = $userService->find($userId);
    
    if ($action === 'update') {
        $userService->update($user);
    }
})->setMaxAttempts(3);
```

## 实际应用场景

### 发送通知

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
})->setMaxAttempts(3);
```

### 处理文件上传

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (FileService $fileService) use ($filePath) {
    $fileService->process($filePath);
    $fileService->generateThumbnail($filePath);
})->delay(5);
```

### 数据统计

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (StatisticsService $stats) use ($date) {
    $stats->calculateDailyReport($date);
    $stats->sendReport($date);
})->onPool('statistics');
```

### 批量操作

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userIds = [1, 2, 3, 4, 5];

foreach ($userIds as $userId) {
    dispatch(function (UserService $userService) use ($userId) {
        $userService->syncUserData($userId);
    })->delay(10 * $userId); // 为每个任务设置不同的延迟
}
```

## API 参考

### `dispatch(Closure $closure): PendingAsyncQueueDispatch`

主要的分发函数，用于创建闭包任务。

**参数：**
- `$closure` - 要执行的闭包

**返回：**
- `PendingAsyncQueueDispatch` - 待处理的闭包分发对象

### `PendingAsyncQueueDispatch` 方法

#### `onPool(string $pool): static`

设置队列连接名称。

**参数：**
- `$pool` - 队列连接名称

**返回：**
- `static` - 当前对象，支持链式调用

#### `delay(int $delay): static`

设置延迟执行时间。

**参数：**
- `$delay` - 延迟时间（秒）

**返回：**
- `static` - 当前对象，支持链式调用

#### `setMaxAttempts(int $maxAttempts): static`

设置最大重试次数。

**参数：**
- `$maxAttempts` - 最大尝试次数

**返回：**
- `static` - 当前对象，支持链式调用

#### `when($condition, $callback): static`

当条件为真时执行回调。

**参数：**
- `$condition` - 条件表达式
- `$callback` - 回调函数，接收当前对象作为参数

**返回：**
- `static` - 当前对象，支持链式调用

#### `unless($condition, $callback): static`

当条件为假时执行回调。

**参数：**
- `$condition` - 条件表达式
- `$callback` - 回调函数，接收当前对象作为参数

**返回：**
- `static` - 当前对象，支持链式调用

## 支持的闭包类型

该组件支持以下类型的闭包：

- ✅ 无参数的简单闭包
- ✅ 带依赖注入的闭包
- ✅ 使用捕获变量（`use`）的闭包
- ✅ 带可空参数的闭包
- ✅ 混合依赖注入和捕获变量的闭包

## 注意事项

1. **序列化限制**：闭包会被序列化后存储，因此：
   - 不能捕获无法序列化的资源（如数据库连接、文件句柄等）
   - 捕获的对象应该是可序列化的

2. **依赖注入**：闭包中的依赖会在任务执行时从容器中解析，不会被序列化

3. **异步执行**：任务是异步执行的，dispatch 函数会立即返回，不会等待任务完成

4. **错误处理**：任务执行失败时会根据 `setMaxAttempts` 设置的次数进行重试

## 配置

该组件使用 Hyperf 的异步队列配置，你可以在 `config/autoload/async_queue.php` 中配置队列参数：

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 10,
        'processes' => 1,
    ],
];
```

## 测试

```shell
composer test:unit -- tests/AsyncQueueClosureJob
```

## 与传统任务类的对比

### 传统方式

```php
// 需要创建任务类
class SendNotificationJob extends Job
{
    public function __construct(public int $userId, public string $message)
    {
    }

    public function handle()
    {
        $notification = ApplicationContext::getContainer()->get(NotificationService::class);
        $notification->send($this->userId, $this->message);
    }
}

// 分发任务
$driver->push(new SendNotificationJob($userId, $message));
```

### 使用闭包任务

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 直接使用闭包，无需创建类
dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
});
```

闭包任务的优势：
- 代码更简洁，无需创建额外的类文件
- 更好的可读性，任务逻辑就在分发的地方
- 完整支持依赖注入
- 灵活的流式 API 配置

## 相关组件

- [hyperf/async-queue](https://hyperf.wiki/3.1/#/zh-cn/async-queue) - Hyperf 异步队列
- [friendsofhyperf/closure-job](https://github.com/friendsofhyperf/components/tree/main/src/closure-job) - 通用闭包任务组件
