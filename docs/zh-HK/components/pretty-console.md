# Pretty Console

The pretty console component for Hyperf.

![Pretty Console](https://user-images.githubusercontent.com/5457236/178333036-b11abb56-ba70-4c0d-a2f6-79afe3a0a78c.png)

## 安裝

```shell
composer require friendsofhyperf/pretty-console
```

## 使用

```php
<?php
use FriendsOfHyperf\PrettyConsole\Traits\Prettyable;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    use Prettyable;

    public function function handle()
    {
        $this->components->info('Your message here.');
    }
}
```

## 鳴謝

- [nunomaduro/termwind](https://github.com/nunomaduro/termwind)
- [The idea from pr of laravel](https://github.com/laravel/framework/pull/43065)
