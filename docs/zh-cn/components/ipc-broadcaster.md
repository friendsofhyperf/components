# Ipc Broadcaster

在 Hyperf Server Worker 和用户进程之间广播可序列化消息。

## 安装

```shell
composer require friendsofhyperf/ipc-broadcaster
```

该包会自动注册 `ConfigProvider`，将 `BroadcasterInterface` 绑定到
`AllProcessesBroadcaster`，并注册 Server Worker 接收消息所需的监听器。该组件没有需要发布的配置文件。

该包的 `composer.json` 仅声明了 `hyperf/event ~3.2.0`，且没有声明可选依赖。请在提供广播器所需
Server、Process、容器和 DI 运行时类的 Hyperf Server 应用中使用。

## 广播消息

`broadcast()` 函数接受 `IpcMessageInterface` 实例或闭包。默认通过
`AllProcessesBroadcaster` 将消息发送到其他所有 Server Worker 和所有已注册的协程用户进程。

### 类消息

继承 `IpcMessage` 并实现 `handle()`。`IpcMessage` 还通过
`InteractsWithFromWorkerId` 提供 `getFromWorkerId()` 和 `setFromWorkerId()`。Server Worker
收到消息时，`getFromWorkerId()` 包含发送方的 Worker ID。

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

消息对象及其属性必须可序列化，才能跨进程传输。

### 闭包消息

闭包在广播前会包装为 `ClosureIpcMessage` 并进行序列化，因此闭包捕获的值也必须可序列化。在常规
Hyperf DI 环境中，有类型声明的闭包参数可从容器解析。

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

## 选择目标

注入 `BroadcasterInterface` 可使用默认的全进程广播行为，也可以构造特定广播器来选择目标：

```php
use FriendsOfHyperf\IpcBroadcaster\ServerBroadcaster;
use FriendsOfHyperf\IpcBroadcaster\UserProcessesBroadcaster;

$serverBroadcaster = new ServerBroadcaster($container, id: 1);
$serverBroadcaster->broadcast($message);

$userProcessBroadcaster = new UserProcessesBroadcaster(name: 'reporting', id: 0);
$userProcessBroadcaster->broadcast($message);
```

`ServerBroadcaster` 接受容器和可选的 Worker ID；未传 ID 时，会发送到当前 Worker 之外的所有
Server Worker。`UserProcessesBroadcaster` 接受可选的进程名称和进程 ID；两者均未传时，会发送到
Hyperf `ProcessCollector` 收集的所有已注册进程。

## 在用户进程中处理消息

组件会为 Server Worker 收到的消息自动调用 `handle()`。在用户进程中，Hyperf 会派发
`Hyperf\Process\Event\PipeMessage`，组件不会自动调用其中消息的 `handle()` 方法。需要让用户进程
执行消息时，请注册监听器：

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

## 在当前 Worker 中运行

广播默认不会在当前 Server Worker 中执行。给消息添加 `RunsInCurrentWorker` 后，会先在当前 Worker
中调用一次 `handle()`，再发送到所选目标：

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

## 协程服务器限制

触发 `MainCoroutineServerStart` 后，跨进程广播会被禁用，广播器调用不会发送消息便直接返回。使用
`RunsInCurrentWorker` 的消息仍会在广播器返回前在本地执行一次。
