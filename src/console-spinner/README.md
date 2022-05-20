# console-spinner

[![Open in Visual Studio Code](https://open.vscode.dev/badges/open-in-vscode.svg)](https://open.vscode.dev/friendsofhyperf/console-spinner)
[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/console-spinner/version.png)](https://packagist.org/packages/friendsofhyperf/console-spinner)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/console-spinner/d/total.png)](https://packagist.org/packages/friendsofhyperf/console-spinner)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/console-spinner)](https://github.com/friendsofhyperf/console-spinner)

## Installation

```bash
composer require friendsofhyperf/console-spinner
```

## Publish

- Optional

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

## Usage

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

The $spinner is compatible with Symfony ProgressBar, so you can run any method of this class.

Or you can also use with withSpinner method by giving an iterable.

```php
$this->withSpinner(User::all(), function($user) {
    // Do your stuff with $user
}, 'Loading...');
```
