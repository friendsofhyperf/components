# Async Queue Closure Job（異步隊列閉包任務）

## 簡介

`friendsofhyperf/async-queue-closure-job` 是一個用於 Hyperf 的異步隊列閉包任務元件。它容許你將閉包作為背景任務執行，完整支援依賴注入和流式配置，讓異步任務的使用變得更加簡單和優雅。

與傳統的建立任務類別方式不同，該元件容許你直接使用閉包來定義任務邏輯，無需建立額外的類別檔案，使代碼更加簡潔。

## 安裝

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## 基礎用法

### 簡單的閉包任務

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 分發一個簡單的閉包任務
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

透過鏈式調用的方式，你可以靈活地配置任務的各種選項：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 鏈式配置多個選項
dispatch(function () {
    // 你的任務邏輯
})
    ->onConnection('high-priority')  // 指定隊列連線
    ->delay(60)                      // 延遲 60 秒執行
    ->setMaxAttempts(5);             // 最多重試 5 次
```

### 指定隊列連線

當你有多個隊列連線時，可以指定任務使用的連線：

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 使用指定的隊列連線
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

// 僅當條件為 true 時執行回撥
dispatch(function () {
    // 你的任務邏輯
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onConnection('urgent');
    });

// 僅當條件為 false 時執行回撥
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
    $logger->info('正在處理 ' . count($users) . ' 個用戶');

    foreach ($users as $user) {
        // 處理用戶...
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

### 處理檔案上載

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (FileService $fileService) use ($filePath) {
    $fileService->process($filePath);
    $fileService->generateThumbnail($filePath);
})->delay(5);
```

### 數據統計

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

### `dispatch(Closure $closure): PendingAsyncQueueDispatch`

主要的分發函數，用於建立閉包任務。

**參數：**
- `$closure` - 要執行的閉包

**返回：**
- `PendingAsyncQueueDispatch` - 待處理的閉包分發物件

### `PendingAsyncQueueDispatch` 方法

#### `onConnection(string $connection): static`

設定隊列連線名稱。

**參數：**
- `$connection` - 隊列連線名稱

**返回：**
- `static` - 目前物件，支援鏈式調用

#### `onPool(string $pool): static`

設定隊列連線名稱（`onConnection` 的別名）。

**參數：**
- `$pool` - 隊列連線名稱

**返回：**
- `static` - 目前物件，支援鏈式調用

#### `delay(int $delay): static`

設定延遲執行時間。

**參數：**
- `$delay` - 延遲時間（秒）

**返回：**
- `static` - 目前物件，支援鏈式調用

#### `setMaxAttempts(int $maxAttempts): static`

設定最大重試次數。

**參數：**
- `$maxAttempts` - 最大嘗試次數

**返回：**
- `static` - 目前物件，支援鏈式調用

#### `when($condition, $callback): static`

當條件為真時執行回撥。

**參數：**
- `$condition` - 條件表達式
- `$callback` - 回撥函數，接收目前物件作為參數

**返回：**
- `static` - 目前物件，支援鏈式調用

#### `unless($condition, $callback): static`

當條件為假時執行回撥。

**參數：**
- `$condition` - 條件表達式
- `$callback` - 回撥函數，接收目前物件作為參數

**返回：**
- `static` - 目前物件，支援鏈式調用

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

2. **依賴注入**：閉包中的依賴會在任務執行時從容器中解析，不會被序列化

3. **異步執行**：任務是異步執行的，dispatch 函數會立即返回，不會等待任務完成

4. **錯誤處理**：任務執行失敗時會根據 `setMaxAttempts` 設定的次數進行重試

## 配置

該元件使用 Hyperf 的異步隊列配置，你可以在 `config/autoload/async_queue.php` 中配置隊列參數：

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

## 與傳統任務類別的比較

### 傳統方式

```php
// 需要建立任務類別
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

// 分發任務
$driver->push(new SendNotificationJob($userId, $message));
```

### 使用閉包任務

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// 直接使用閉包，無需建立類別
dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
});
```

閉包任務的優勢：
- 代碼更簡潔，無需建立額外的類別檔案
- 更好的可讀性，任務邏輯就在分發的地方
- 完整支援依賴注入
- 靈活的流式 API 配置

## 相關元件

- [hyperf/async-queue](https://hyperf.wiki/3.1/#/zh-cn/async-queue) - Hyperf 異步隊列
- [friendsofhyperf/closure-job](https://github.com/friendsofhyperf/components/tree/main/src/closure-job) - 通用閉包任務元件
