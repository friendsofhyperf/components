# Console Spinner

用於 Hyperf 命令的控制枱 Spinner 組件。它封裝了 Symfony `ProgressBar`，並在每次推進時切換
進度字符。

## 安裝

```shell
composer require friendsofhyperf/console-spinner
```

## 可選配置

僅在需要自定義 Spinner 字符時發佈配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

發佈後的 `config/autoload/console_spinner.php` 文件包含：

```php
return [
    'chars' => ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'],
];
```

請確保 `chars` 是非空數組，因為 Spinner 每次推進時都會從中選擇一個字符。

## 使用

在 Hyperf 命令中使用 `Spinnerable` Trait。它的受保護輔助方法 `spinner(int $max = 0)` 會創建
一個具有指定最大步數的 Spinner：

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

`Spinner::advance(int $step = 1)` 會切換 Spinner 字符並推進封裝的進度條。調用其他方法時，會將
調用轉發給 Symfony `ProgressBar`；需要直接訪問底層實例時，可使用 `getOriginalProgressBar()`。

## 使用 `withSpinner` 處理步驟

受保護輔助方法 `withSpinner($totalSteps, Closure $callback, string $message = '')` 會自動啓動
和結束 Spinner。

當 `$totalSteps` 是數組等可計數的可迭代對象時，回調會接收當前元素和 Spinner，並在處理每個
元素後自動推進 Spinner：

```php
$this->withSpinner(User::all(), function ($user, $spinner): void {
    // Do your stuff with $user...
}, 'Loading...');
```

當 `$totalSteps` 是整數時，回調只接收 Spinner，並負責推進它：

```php
$this->withSpinner(10, function ($spinner): void {
    for ($i = 0; $i < 10; ++$i) {
        // Do your stuff...
        $spinner->advance();
    }
}, 'Loading...');
```
