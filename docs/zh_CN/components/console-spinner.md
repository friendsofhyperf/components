# console-spinner

The progress bar component For Hyperf.

## 安装

```shell
composer require friendsofhyperf/console-spinner
```

## 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

## 使用

```php
class FooCommand extends Command
{
    use Spinnerable;

    /**
     * Execute the console command.
     *
     * @return void
     */
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

`$spinner` 兼容 Symfony ProgressBar，因此您可以运行此类的任何方法。或者，您也可以通过提供一个可迭代对象来使用 `withSpinner` 方法。

```php
$this->withSpinner(User::all(), function($user) {
    // Do your stuff with $user
}, 'Loading...');
```
