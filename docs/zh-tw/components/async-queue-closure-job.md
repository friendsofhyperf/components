# Async Queue Closure Job（非同步佇列閉包任務）

## 簡介

`friendsofhyperf/async-queue-closure-job` 是一個用於 Hyperf 的非同步佇列閉包任務元件。它允許你將閉包作為背景工作執行，完整支援依賴注入和流式配置，讓非同步任務的使用變得更加簡單和優雅。

與傳統的建立任務類別方式不同，該元件允許你直接使用閉包來定義任務邏輯，無需建立額外的類別檔案，使程式碼更加簡潔。

## 安裝

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## 基礎用法

### 簡單的閉包任務

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 分派一個簡單的閉包任務
dispatch(function () {
    // 你的任務邏輯
    var_dump('Hello from closure job!');
});
```

### 設定最大嘗試次數

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 設定最大嘗試次數（重試限制）
dispatch(function () {
    // 你的任務邏輯
    // 如果失敗，將重試最多 3 次
})->setMaxAttempts(3);
```

## 進階用法

### 流式 API 配置

透過鏈式呼叫的方式，你可以靈活地配置任務的各種選項：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 鏈式配置多個選項
dispatch(function () {
    // 你的任務邏輯
})
    ->onConnection('high-priority')  // 指定佇列連線
    ->delay(60)                      // 延遲 60 秒執行
    ->setMaxAttempts(5);             // 最多重試 5 次
```

### 指定佇列連線

當你有多個佇列連線時，可以指定任務使用的連線：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 使用指定的佇列連線
dispatch(function () {
    // 高優先級任務邏輯
})->onConnection('high-priority');

// 或者使用 onPool 方法（別名）
dispatch(function () {
    // 低優先級任務邏輯
})->onPool('low-priority');
```

### 延遲執行

你可以設定任務在一段時間後才開始執行：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 延遲 60 秒後執行
dispatch(function () {
    // 你的任務邏輯
})->delay(60);

// 延遲 5 分鐘後執行
dispatch(function () {
    // 你的任務邏輯
})->delay(300);
```

### 條件執行

使用 `when` 和 `unless` 方法，可以根據條件動態配置任務：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$isUrgent = true;

// 僅當條件為 true 時執行回呼
dispatch(function () {
    // 你的任務邏輯
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onConnection('urgent');
    });

// 僅當條件為 false 時執行回呼
dispatch(function () {
    // 你的任務邏輯
})
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(300);
    });

// 組合使用
dispatch(function () {
    // 你的任務邏輯
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onConnection('urgent');
    })
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(60);
    });
```

### 依賴注入

閉包任務完整支援 Hyperf 的依賴注入功能，你可以在閉包參數中宣告需要的依賴：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;
use App\Service\UserService;
use Psr\Log\LoggerInterface;

// 自動依賴注入
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('正在處理 ' . count($users) . ' 個使用者');

    foreach ($users as $user) {
        // 處理使用者...
    }
});
```

### 使用捕獲變數

你可以透過 `use` 關鍵字在閉包中使用外部變數：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userId = 123;
$action = 'update';

// 使用捕獲變數
dispatch(function (UserService $userService) use ($userId, $action) {
    $user = $userService->find($userId);

    if ($action === 'update') {
        $userService->update($user);
    }
})->setMaxAttempts(3);
```

## 實際應用場景

### 傳送通知

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
})->setMaxAttempts(3);
```

### 處理檔案上傳

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (FileService $fileService) use ($filePath) {
    $fileService->process($filePath);
    $fileService->generateThumbnail($filePath);
})->delay(5);
```

### 資料統計

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (StatisticsService $stats) use ($date) {
    $stats->calculateDailyReport($date);
    $stats->sendReport($date);
})->onConnection('statistics');
```

### 批次操作

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userIds = [1, 2, 3, 4, 5];

foreach ($userIds as $userId) {
    dispatch(function (UserService $userService) use ($userId) {
        $userService->syncUserData($userId);
    })->delay(10 * $userId); // 為每個任務設定不同的延遲
}
```

## API 參考

### `dispatch(Closure $closure): PendingClosureDispatch`

主要的分發函式，用於建立閉包任務。

**參數：**
- `$closure` - 要執行的閉包

**回傳：**
- `PendingClosureDispatch` - 待處理的閉包分發物件

### `PendingClosureDispatch` 方法

#### `onConnection(string $connection): static`

設定佇列連線名稱。

**參數：**
- `$connection` - 佇列連線名稱

**回傳：**
- `static` - 目前物件，支援鏈式呼叫

#### `onPool(string $pool): static`

設定佇列連線名稱（`onConnection` 的別名）。

**參數：**
- `$pool` - 佇列連線名稱

**回傳：**
- `static` - 目前物件，支援鏈式呼叫

#### `delay(int $delay): static`

設定延遲執行時間。

**參數：**
- `$delay` - 延遲時間（秒）

**回傳：**
- `static` - 目前物件，支援鏈式呼叫

#### `setMaxAttempts(int $maxAttempts): static`

設定最大重試次數。

**參數：**
- `$maxAttempts` - 最大嘗試次數

**回傳：**
- `static` - 目前物件，支援鏈式呼叫

#### `when($condition, $callback): static`

當條件為真時執行回呼。

**參數：**
- `$condition` - 條件表達式
- `$callback` - 回呼函式，接收目前物件作為參數

**回傳：**
- `static` - 目前物件，支援鏈式呼叫

#### `unless($condition, $callback): static`

當條件為假時執行回呼。

**參數：**
- `$condition` - 條件表達式
- `$callback` - 回呼函式，接收目前物件作為參數

**回傳：**
- `static` - 目前物件，支援鏈式呼叫

## 支援的閉包類型

該元件支援以下類型的閉包：

- ✅ 無參數的簡單閉包
- ✅ 帶依賴注入的閉包
- ✅ 使用捕獲變數（`use`）的閉包
- ✅ 帶可空參數的閉包
- ✅ 混合依賴注入和捕獲變數的閉包

## 注意事項

1. **序列化限制**：閉包會被序列化後儲存，因此：
   - 不能捕獲無法序列化的資源（如資料庫連線、檔案句柄等）
   - 捕獲的物件應該是可序列化的

2. **依賴注入**：閉包中的依賴會在工作執行時從容器中解析，不會被序列化

3. **非同步執行**：工作是非同步執行的，dispatch 函式會立即返回，不會等待工作完成

4. **錯誤處理**：工作執行失敗時會根據 `setMaxAttempts` 設定的次數進行重試

## 配置

該元件使用 Hyperf 的非同步佇列配置，你可以在 `config/autoload/async_queue.php` 中配置佇列參數：

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

## 測試

```shell
composer test:unit -- tests/AsyncQueueClosureJob
```

## 與傳統工作類別的比較

### 傳統方式

```php
// 需要建立工作類別
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

// 分派工作
$driver->push(new SendNotificationJob($userId, $message));
```

### 使用閉包工作

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 直接使用閉包，無需建立類別
dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
});
```

閉包工作的優勢：
- 程式碼更簡潔，無需建立額外的類別檔案
- 更好的可讀性，工作邏輯就在分派的地方
- 完整支援依賴注入
- 靈活的流式 API 配置

## 相關元件

- [hyperf/async-queue](https://hyperf.wiki/3.1/#/zh-cn/async-queue) - Hyperf 非同步佇列
- [friendsofhyperf/closure-job](https://github.com/friendsofhyperf/components/tree/main/src/closure-job) - 通用閉包工作元件
