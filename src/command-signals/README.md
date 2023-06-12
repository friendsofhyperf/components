# Command Signals

[![Latest Test](https://github.com/friendsofhyperf/command-signals/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/command-signals/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/command-signals)](https://packagist.org/packages/friendsofhyperf/command-signals)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/command-signals)](https://packagist.org/packages/friendsofhyperf/command-signals)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/command-signals)](https://github.com/friendsofhyperf/command-signals)

The signals component for Hyperf Command.

## Requirements

- PHP >= 8.0
- Hyperf >= 3.0

## Installation

```shell
composer require friendsofhyperf/command-signals
```

## Usage

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
        $this->trap([SIGINT, SIGTERM], function ($signo) {
            $this->warn(sprintf('Received signal %d, exiting...', $signo));
        });

        sleep(10);

        $this->info('Bye!');
    }
}
```

## Run

- `Ctrl + C`

```shell
$ hyperf foo
^CReceived signal 2, exiting...
```

- `killall php`

```shell
$ hyperf foo
Received signal 15, exiting...
[1]    51936 terminated  php bin/hyperf.php foo
```

## Sponsor

If you like this project, Buy me a cup of coffee. [ [Alipay](https://hdj.me/images/alipay.jpg) | [WePay](https://hdj.me/images/wechat-pay.jpg) ]
