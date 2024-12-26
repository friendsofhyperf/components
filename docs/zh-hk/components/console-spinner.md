# Console Spinner

The progress bar component For Hyperf.

## 安裝

```shell
composer require friendsofhyperf/console-spinner
```

## 發佈配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

## 使用

```php
class FooCommand extends Command
{
    use Spinnerable;

    public function handle()
    {
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

`$spinner` 兼容 Symfony ProgressBar，因此您可以運行此類的任何方法。或者，您也可以通過提供一個可迭代對象來使用 `withSpinner` 方法。

```php
$this->withSpinner(User::all(), function($user) {
    // Do your stuff with $user
}, 'Loading...');
```
