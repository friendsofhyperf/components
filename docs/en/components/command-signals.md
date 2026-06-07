# Command Signals

`friendsofhyperf/command-signals` adds coroutine-based POSIX signal handling to
Hyperf commands.

## Installation

```shell
composer require friendsofhyperf/command-signals
```

The package supports Hyperf 3.2 and is discovered automatically. Its
`ConfigProvider` does not publish any configuration. The command example below
assumes that the application already has `hyperf/command`; this package does
not declare it as a dependency.

The implementation uses Hyperf Engine signals and calls `posix_getpid()` and
`posix_kill()`. Run it in a coroutine-capable Swoole or Swow environment that
supports POSIX signals. The PHP POSIX extension must provide those two
functions. Composer suggests `ext-swoole >= 4.6.0` or `ext-swow >= 0.1.0`, but
does not declare the POSIX extension as a dependency.

## Usage

Add `InteractsWithSignals` to a command and call `trap()` with one signal number
or an array of signal numbers. The callback receives the signal number.

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

`trap()` creates a `SignalRegistry` through the container on its first call and
automatically clears its callbacks when the current coroutine ends. Multiple
callbacks may be registered for the same signal; they run concurrently when
that signal is received.

Each registered signal is handled once. After its callbacks finish, the
registry stops waiting and sends the same signal to the current process again,
so the operating system will normally apply the signal's default action.

Use `untrap()` to clear callbacks for one signal, several signals, or all
signals. It does not cancel a waiter that has already started; if that signal
later arrives, the registry still sends it to the process again after running
no callbacks.

```php
$this->untrap(SIGINT);
$this->untrap([SIGINT, SIGTERM]);
$this->untrap();
```

## API

The trait exposes these protected methods to the command:

- `trap(array|int $signo, callable $callback): void`
- `untrap(null|array|int $signo = null): void`

`SignalRegistry` is also public. Its constructor is
`__construct(int $timeout = 1, int $concurrentLimit = 0)`, where `timeout` is
the timeout in seconds for each wait attempt and `concurrentLimit` limits
concurrently executing callbacks. A limit of `0` means unlimited concurrency.
It provides:

- `register(int|array $signo, callable $signalHandler): void`
- `unregister(null|int|array $signo = null): void`

`unregister()` clears the callbacks for the selected signals; passing `null`
clears all callbacks.

## Execution

- Press `Ctrl + C` to send `SIGINT`.

```shell
$ hyperf foo
^CReceived signal 2, exiting...
```

- Send `SIGTERM`, for example with `killall php`.

```shell
$ hyperf foo
Received signal 15, exiting...
[1]    51936 terminated  php bin/hyperf.php foo
```
