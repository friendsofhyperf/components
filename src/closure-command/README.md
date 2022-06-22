# closure-command

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/closure-command)](https://packagist.org/packages/friendsofhyperf/closure-command)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/closure-command)](https://packagist.org/packages/friendsofhyperf/closure-command)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/closure-command)](https://github.com/friendsofhyperf/closure-command)

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

use function FriendsOfHyperf\ClosureCommand\command;

Console::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Console::command('foo:bar', function() {
    $this->info('Command foo:bar executed.');
})->describe('Description of command foo::bar');

command('whoami', function () {
    $this->info('Your are friend of hyperf');
})->describe('Who am I');
```

- Define AnnotationCommand

```php
use FriendsOfHyperf\ClosureCommand\Annotation\Command;
use Hyperf\Contract\StdoutLoggerInterface;
use FriendsOfHyperf\ClosureCommand\Input;
use FriendsOfHyperf\ClosureCommand\Output;

class Foo
{
    #[Inject()]
    protected Input $input;

    #[Inject()]
    protected Output $output;

    #[Command(signature: 'foo:bar {--bar=1}', description: 'Test foo::bar')]
    public function bar($bar)
    {
        $this->output->info('$bar:' . $this->input->getOption('bar'));
        $this->output->info('$bar:' . $bar);
        $this->output->info('foo::bar executed.');
    }
}
```
