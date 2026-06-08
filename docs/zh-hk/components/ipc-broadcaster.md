# Ipc Broadcaster

在 Hyperf Server Worker 和用戶進程之間廣播可序列化訊息。

## 安裝

```shell
composer require friendsofhyperf/ipc-broadcaster
```

該套件會自動註冊 `ConfigProvider`，將 `BroadcasterInterface` 綁定到
`AllProcessesBroadcaster`，並註冊 Server Worker 接收訊息所需的監聽器。該組件沒有需要發佈的配置檔案。

該套件的 `composer.json` 僅聲明了 `hyperf/event ~3.2.0`，且沒有聲明可選依賴。請在提供廣播器所需
Server、Process、容器和 DI 運行時類的 Hyperf Server 應用中使用。

## 廣播訊息

`broadcast()` 函數接受 `IpcMessageInterface` 實例或閉包。默認通過
`AllProcessesBroadcaster` 將訊息發送到其他所有 Server Worker 和所有已註冊的協程用戶進程。

### 類訊息

繼承 `IpcMessage` 並實現 `handle()`。`IpcMessage` 還通過
`InteractsWithFromWorkerId` 提供 `getFromWorkerId()` 和 `setFromWorkerId()`。Server Worker
收到訊息時，`getFromWorkerId()` 包含發送方的 Worker ID。

```php
<?php

namespace App\Broadcasting;

use FriendsOfHyperf\IpcBroadcaster\IpcMessage;

use function FriendsOfHyperf\IpcBroadcaster\broadcast;

class FooMessage extends IpcMessage
{
    public function __construct(private string $foo)
    {
    }

    public function handle(): void
    {
        echo $this->foo;
    }
}

broadcast(new FooMessage('bar'));
```

訊息物件及其屬性必須可序列化，才能跨進程傳輸。

### 閉包訊息

閉包在廣播前會包裝為 `ClosureIpcMessage` 並進行序列化，因此閉包捕獲的值也必須可序列化。在常規
Hyperf DI 環境中，有類型聲明的閉包參數可從容器解析。

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

## 選擇目標

注入 `BroadcasterInterface` 可使用默認的全進程廣播行為，也可以構造特定廣播器來選擇目標：

```php
use FriendsOfHyperf\IpcBroadcaster\ServerBroadcaster;
use FriendsOfHyperf\IpcBroadcaster\UserProcessesBroadcaster;

$serverBroadcaster = new ServerBroadcaster($container, id: 1);
$serverBroadcaster->broadcast($message);

$userProcessBroadcaster = new UserProcessesBroadcaster(name: 'reporting', id: 0);
$userProcessBroadcaster->broadcast($message);
```

`ServerBroadcaster` 接受容器和可選的 Worker ID；未傳 ID 時，會發送到當前 Worker 之外的所有
Server Worker。`UserProcessesBroadcaster` 接受可選的進程名稱和進程 ID；兩者均未傳時，會發送到
Hyperf `ProcessCollector` 收集的所有已註冊進程。

## 在用戶進程中處理訊息

組件會為 Server Worker 收到的訊息自動調用 `handle()`。在用戶進程中，Hyperf 會派發
`Hyperf\Process\Event\PipeMessage`，組件不會自動調用其中訊息的 `handle()` 方法。需要讓用戶進程
執行訊息時，請註冊監聽器：

```php
use FriendsOfHyperf\IpcBroadcaster\Contract\IpcMessageInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\PipeMessage;

class UserProcessPipeMessageListener implements ListenerInterface
{
    public function listen(): array
    {
        return [PipeMessage::class];
    }

    public function process(object $event): void
    {
        if ($event instanceof PipeMessage && $event->data instanceof IpcMessageInterface) {
            $event->data->handle();
        }
    }
}
```

## 在當前 Worker 中運行

廣播默認不會在當前 Server Worker 中執行。給訊息添加 `RunsInCurrentWorker` 後，會先在當前 Worker
中調用一次 `handle()`，再發送到所選目標：

```php
use FriendsOfHyperf\IpcBroadcaster\IpcMessage;
use FriendsOfHyperf\IpcBroadcaster\Traits\RunsInCurrentWorker;

class RefreshMessage extends IpcMessage
{
    use RunsInCurrentWorker;

    public function handle(): void
    {
        // Refresh local state.
    }
}
```

## 協程伺服器限制

觸發 `MainCoroutineServerStart` 後，跨進程廣播會被禁用，廣播器調用不會發送訊息便直接返回。使用
`RunsInCurrentWorker` 的訊息仍會在廣播器返回前在本地執行一次。
