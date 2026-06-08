# Command Signals

`friendsofhyperf/command-signals` 為 Hyperf 指令提供基於協程的 POSIX 訊號處理能力。

## 安裝

```shell
composer require friendsofhyperf/command-signals
```

該組件支援 Hyperf 3.2，並會被自動發現。它的 `ConfigProvider` 不會發佈任何配置。下方
指令範例假設應用程式已經安裝 `hyperf/command`；該組件沒有將其聲明為依賴。

實現透過 Hyperf Engine 處理訊號，並調用 `posix_getpid()` 和 `posix_kill()`。請在支援
POSIX 訊號且可運行協程的 Swoole 或 Swow 環境中使用。PHP POSIX 擴展必須提供上述兩個
函式。Composer 建議安裝 `ext-swoole >= 4.6.0` 或 `ext-swow >= 0.1.0`，但沒有將
POSIX 擴展聲明為依賴。

## 使用

在指令中引入 `InteractsWithSignals`，然後調用 `trap()` 並傳入一個訊號編號或訊號編號陣列。
回調會接收到訊號編號。

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

首次調用 `trap()` 時，它會透過容器建立 `SignalRegistry`，並在目前協程結束時自動清空回調。
同一個訊號可以註冊多個回調；收到該訊號時，這些回調會並行執行。

每個已註冊訊號只會被處理一次。回調執行完成後，註冊器會停止等待，並再次向目前程序發送同一
訊號，因此作業系統通常會執行該訊號的預設動作。

使用 `untrap()` 清空一個訊號、多個訊號或全部訊號的回調。它不會取消已經啟動的等待協程；
如果之後收到該訊號，註冊器仍會在不執行回調後再次向程序發送該訊號。

```php
$this->untrap(SIGINT);
$this->untrap([SIGINT, SIGTERM]);
$this->untrap();
```

## API

Trait 向指令提供以下受保護方法：

- `trap(array|int $signo, callable $callback): void`
- `untrap(null|array|int $signo = null): void`

`SignalRegistry` 也是公開類別。其建構函式為
`__construct(int $timeout = 1, int $concurrentLimit = 0)`；`timeout` 是每次等待嘗試的
逾時秒數，`concurrentLimit` 用於限制並行執行的回調數量，值為 `0` 表示不限制並行數。它
提供以下方法：

- `register(int|array $signo, callable $signalHandler): void`
- `unregister(null|int|array $signo = null): void`

`unregister()` 會清空所選訊號的回調；傳入 `null` 會清空全部回調。

## 運行

- 按下 `Ctrl + C` 發送 `SIGINT`。

```shell
$ hyperf foo
^CReceived signal 2, exiting...
```

- 發送 `SIGTERM`，例如使用 `killall php`。

```shell
$ hyperf foo
Received signal 15, exiting...
[1]    51936 terminated  php bin/hyperf.php foo
```
