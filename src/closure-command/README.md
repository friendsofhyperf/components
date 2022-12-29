# closure-command

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/closure-command)](https://packagist.org/packages/friendsofhyperf/closure-command)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/closure-command)](https://packagist.org/packages/friendsofhyperf/closure-command)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/closure-command)](https://github.com/friendsofhyperf/closure-command)

The closure command component for Hyperf.

## Installation

```bash
composer require friendsofhyperf/closure-command
```

## Publish

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/closure-command
```

## Usage

- Define ClosureCommand

```php
// config/console.php

use FriendsOfHyperf\ClosureCommand\Console;
use FriendsOfHyperf\ClosureCommand\Inspiring;

Console::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Console::command('foo:bar', function() {
    $this->info('Command foo:bar executed.');
})->describe('Description of command foo::bar');
```

- Define AnnotationCommand

```php
<?php

namespace App\Service;

use FriendsOfHyperf\ClosureCommand\Annotation\Command;
use FriendsOfHyperf\ClosureCommand\Output;
use Hyperf\Di\Annotation\Inject;

#[Command(signature: 'foo:bar1', handle: 'bar1', description: 'The description of foo:bar1 command.')]
#[Command(signature: 'foo', description: 'The description of foo command.')]
class FooService
{
    #[Inject()]
    protected Output $output;

    #[Command(signature: 'foo:bar {--bar=1 : Bar Value}', description: 'The description of foo:bar command.')]
    public function bar($bar)
    {
        $this->output->info('Bar Value: ' . $bar);

        return $bar;
    }

    public function bar1()
    {
        $this->output->info(__METHOD__);
    }

    public function handle()
    {
        $this->output->info(__METHOD__);
    }
}
```

Run `php bin/hyperf.php`

```shell
foo
  foo:bar                   The description of foo:bar command.
  foo:bar1                  The description of foo:bar1 command.
```
