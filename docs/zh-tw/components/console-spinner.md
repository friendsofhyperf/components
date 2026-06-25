# Console Spinner

用於 Hyperf 命令的控制檯 Spinner 元件。它封裝了 Symfony `ProgressBar`，並在每次推進時切換
進度字元。

## 安裝

```shell
composer require friendsofhyperf/console-spinner
```

## 可選配置

僅在需要自定義 Spinner 字元時釋出配置檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

釋出後的 `config/autoload/console_spinner.php` 檔案包含：

```php
return [
    'chars' => ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'],
];
```

請確保 `chars` 是非空陣列，因為 Spinner 每次推進時都會從中選擇一個字元。

## 使用

在 Hyperf 命令中使用 `Spinnerable` Trait。它的受保護輔助方法 `spinner(int $max = 0)` 會建立
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

`Spinner::advance(int $step = 1)` 會切換 Spinner 字元並推進封裝的進度條。呼叫其他方法時，會將
呼叫轉發給 Symfony `ProgressBar`；需要直接訪問底層例項時，可使用 `getOriginalProgressBar()`。

## 使用 `withSpinner` 處理步驟

受保護輔助方法 `withSpinner($totalSteps, Closure $callback, string $message = '')` 會自動啟動
和結束 Spinner。

當 `$totalSteps` 是陣列等可計數的可迭代物件時，回撥會接收當前元素和 Spinner，並在處理每個
元素後自動推進 Spinner：

```php
$this->withSpinner(User::all(), function ($user, $spinner): void {
    // Do your stuff with $user...
}, 'Loading...');
```

當 `$totalSteps` 是整數時，回撥只接收 Spinner，並負責推進它：

```php
$this->withSpinner(10, function ($spinner): void {
    for ($i = 0; $i < 10; ++$i) {
        // Do your stuff...
        $spinner->advance();
    }
}, 'Loading...');
```
