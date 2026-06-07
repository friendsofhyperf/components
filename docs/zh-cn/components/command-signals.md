# Command Signals

`friendsofhyperf/command-signals` 为 Hyperf 命令提供基于协程的 POSIX 信号处理能力。

## 安装

```shell
composer require friendsofhyperf/command-signals
```

该组件支持 Hyperf 3.2，并会被自动发现。它的 `ConfigProvider` 不会发布任何配置。下方
命令示例假设应用已经安装 `hyperf/command`；该组件没有将其声明为依赖。

实现通过 Hyperf Engine 处理信号，并调用 `posix_getpid()` 和 `posix_kill()`。请在支持
POSIX 信号且可运行协程的 Swoole 或 Swow 环境中使用。PHP POSIX 扩展必须提供上述两个
函数。Composer 建议安装 `ext-swoole >= 4.6.0` 或 `ext-swow >= 0.1.0`，但没有将
POSIX 扩展声明为依赖。

## 使用

在命令中引入 `InteractsWithSignals`，然后调用 `trap()` 并传入一个信号编号或信号编号数组。
回调会接收到信号编号。

```php
namespace App\Command;

use FriendsOfHyperf\CommandSignals\Traits\InteractsWithSignals;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class FooCommand extends HyperfCommand
{
    use InteractsWithSignals;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('foo');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $this->trap([SIGINT, SIGTERM], function (int $signo): void {
            $this->warn(sprintf('Received signal %d, exiting...', $signo));
        });

        sleep(10);

        $this->info('Bye!');
    }
}
```

首次调用 `trap()` 时，它会通过容器创建 `SignalRegistry`，并在当前协程结束时自动清空回调。
同一个信号可以注册多个回调；收到该信号时，这些回调会并发执行。

每个已注册信号只会被处理一次。回调执行完成后，注册器会停止等待，并再次向当前进程发送同一
信号，因此操作系统通常会执行该信号的默认动作。

使用 `untrap()` 清空一个信号、多个信号或全部信号的回调。它不会取消已经启动的等待协程；
如果之后收到该信号，注册器仍会在不执行回调后再次向进程发送该信号。

```php
$this->untrap(SIGINT);
$this->untrap([SIGINT, SIGTERM]);
$this->untrap();
```

## API

Trait 向命令提供以下受保护方法：

- `trap(array|int $signo, callable $callback): void`
- `untrap(null|array|int $signo = null): void`

`SignalRegistry` 也是公开类。其构造函数为
`__construct(int $timeout = 1, int $concurrentLimit = 0)`；`timeout` 是每次等待尝试的
超时秒数，`concurrentLimit` 用于限制并发执行的回调数量，值为 `0` 表示不限制并发数。它
提供以下方法：

- `register(int|array $signo, callable $signalHandler): void`
- `unregister(null|int|array $signo = null): void`

`unregister()` 会清空所选信号的回调；传入 `null` 会清空全部回调。

## 运行

- 按下 `Ctrl + C` 发送 `SIGINT`。

```shell
$ hyperf foo
^CReceived signal 2, exiting...
```

- 发送 `SIGTERM`，例如使用 `killall php`。

```shell
$ hyperf foo
Received signal 15, exiting...
[1]    51936 terminated  php bin/hyperf.php foo
```
