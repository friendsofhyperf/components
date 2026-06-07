# Ipc Broadcaster

Broadcast serializable messages between Hyperf server workers and user processes.

## Installation

```shell
composer require friendsofhyperf/ipc-broadcaster
```

The package automatically registers its `ConfigProvider`. It binds
`BroadcasterInterface` to `AllProcessesBroadcaster` and registers the listeners required
to receive messages in server workers. There is no configuration file to publish.

The package declares `hyperf/event ~3.2.0` and no optional dependencies in its
`composer.json`. Use it in a Hyperf server application that provides the server, process,
container, and DI runtime classes used by the broadcasters.

## Broadcast Messages

The `broadcast()` function accepts an `IpcMessageInterface` instance or a closure. By
default, it uses `AllProcessesBroadcaster` to send the message to all other server workers
and all registered coroutine user processes.

### Class Message

Extend `IpcMessage` and implement `handle()`. `IpcMessage` also provides
`getFromWorkerId()` and `setFromWorkerId()` through `InteractsWithFromWorkerId`. When a
server worker receives the message, `getFromWorkerId()` contains the sending worker ID.

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

Message objects and their properties must be serializable so they can cross process
boundaries.

### Closure Message

Closures are wrapped in `ClosureIpcMessage` and serialized before broadcasting. Values
captured by the closure must therefore also be serializable. In a normal Hyperf DI
environment, typed closure parameters can be resolved from the container.

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

## Select Targets

Inject `BroadcasterInterface` for the default all-processes behavior, or construct a
specific broadcaster to select targets:

```php
use FriendsOfHyperf\IpcBroadcaster\ServerBroadcaster;
use FriendsOfHyperf\IpcBroadcaster\UserProcessesBroadcaster;

$serverBroadcaster = new ServerBroadcaster($container, id: 1);
$serverBroadcaster->broadcast($message);

$userProcessBroadcaster = new UserProcessesBroadcaster(name: 'reporting', id: 0);
$userProcessBroadcaster->broadcast($message);
```

`ServerBroadcaster` accepts a container and an optional worker ID. Without an ID, it sends
to every server worker except the current worker. `UserProcessesBroadcaster` accepts an
optional process name and process ID. Without either, it sends to all registered user
processes collected by Hyperf's `ProcessCollector`.

## Handle Messages in User Processes

The component automatically calls `handle()` for messages received by server workers. In
a user process, Hyperf dispatches a `Hyperf\Process\Event\PipeMessage`; the component does
not automatically call the contained message's `handle()` method. Register a listener when
the user process should execute the message:

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

## Run in the Current Worker

By default, broadcasts do not execute in the current server worker. Add
`RunsInCurrentWorker` to a message to call `handle()` once in the current worker before it
is sent to the selected targets:

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

## Coroutine Server Limitation

After `MainCoroutineServerStart`, cross-process broadcasting is disabled and broadcaster
calls return without sending messages. A message using `RunsInCurrentWorker` still runs
once locally before the broadcaster returns.
