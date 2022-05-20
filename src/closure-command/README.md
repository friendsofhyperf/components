# closure-command

[![Open in Visual Studio Code](https://open.vscode.dev/badges/open-in-vscode.svg)](https://open.vscode.dev/friendsofhyperf/closure-command)
[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/closure-command/version.png)](https://packagist.org/packages/friendsofhyperf/closure-command)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/closure-command/d/total.png)](https://packagist.org/packages/friendsofhyperf/closure-command)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/closure-command)](https://github.com/friendsofhyperf/closure-command)

## Installation

```bash
composer require friendsofhyperf/closure-command
```

## Publish

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/closure-command
```

## Usage

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
