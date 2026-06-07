# Console Spinner

A console spinner component for Hyperf commands. It wraps Symfony's `ProgressBar` and changes
the progress character on each advance.

## Installation

```shell
composer require friendsofhyperf/console-spinner
```

## Optional Configuration

Publish the configuration file only when you need to customize the spinner characters:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

The published `config/autoload/console_spinner.php` file contains:

```php
return [
    'chars' => ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'],
];
```

Keep `chars` as a non-empty array because the spinner selects a character from it whenever it
advances.

## Usage

Use the `Spinnerable` trait in a Hyperf command. Its protected `spinner(int $max = 0)` helper
creates a spinner with the given maximum number of steps:

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

`Spinner::advance(int $step = 1)` changes the spinner character and advances the wrapped progress
bar. Other method calls are forwarded to the Symfony `ProgressBar`; use
`getOriginalProgressBar()` when you need the underlying instance directly.

## Process Steps with `withSpinner`

The protected `withSpinner($totalSteps, Closure $callback, string $message = '')` helper starts
and finishes the spinner automatically.

When `$totalSteps` is a countable iterable such as an array, the callback receives the current
item and the spinner, and the spinner advances after every item:

```php
$this->withSpinner(User::all(), function ($user, $spinner): void {
    // Do your stuff with $user...
}, 'Loading...');
```

When `$totalSteps` is an integer, the callback receives only the spinner. The callback is
responsible for advancing it:

```php
$this->withSpinner(10, function ($spinner): void {
    for ($i = 0; $i < 10; ++$i) {
        // Do your stuff...
        $spinner->advance();
    }
}, 'Loading...');
```
