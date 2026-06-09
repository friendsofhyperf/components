# Console Spinner

[English](README.md)

用于 Hyperf 命令的控制台 Spinner 组件。它封装了 Symfony `ProgressBar`，并在每次推进时切换
进度字符。

## 安装

```shell
composer require friendsofhyperf/console-spinner
```

## 可选配置

仅在需要自定义 Spinner 字符时发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

发布后的 `config/autoload/console_spinner.php` 文件包含：

```php
return [
    'chars' => ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'],
];
```

请确保 `chars` 是非空数组，因为 Spinner 每次推进时都会从中选择一个字符。

## 使用

在 Hyperf 命令中使用 `Spinnerable` Trait。它的受保护辅助方法 `spinner(int $max = 0)` 会创建
一个具有指定最大步数的 Spinner：

```php
use FriendsOfHyperf\ConsoleSpinner\Traits\Spinnerable;
use Hyperf\Command\Command;

class FooCommand extends Command
{
    use Spinnerable;

    public function handle(): void
    {
        $users = User::all();
        $spinner = $this->spinner($users->count());
        $spinner->setMessage('Loading...');
        $spinner->start();

        foreach ($users as $user) {
            // Do your stuff...
            $spinner->advance();
        }

        $spinner->finish();
    }
}
```

`Spinner::advance(int $step = 1)` 会切换 Spinner 字符并推进封装的进度条。调用其他方法时，会将
调用转发给 Symfony `ProgressBar`；需要直接访问底层实例时，可使用 `getOriginalProgressBar()`。

## 使用 `withSpinner` 处理步骤

受保护辅助方法 `withSpinner($totalSteps, Closure $callback, string $message = '')` 会自动启动
和结束 Spinner。

当 `$totalSteps` 是数组等可计数的可迭代对象时，回调会接收当前元素和 Spinner，并在处理每个
元素后自动推进 Spinner：

```php
$this->withSpinner(User::all(), function ($user, $spinner): void {
    // Do your stuff with $user...
}, 'Loading...');
```

当 `$totalSteps` 是整数时，回调只接收 Spinner，并负责推进它：

```php
$this->withSpinner(10, function ($spinner): void {
    for ($i = 0; $i < 10; ++$i) {
        // Do your stuff...
        $spinner->advance();
    }
}, 'Loading...');
```
