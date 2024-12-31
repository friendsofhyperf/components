# Console Spinner

A progress bar component provided for the Hyperf framework.

## Installation

```shell
composer require friendsofhyperf/console-spinner
```

## Publish Configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/console-spinner
```

## Usage

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

The `$spinner` is compatible with Symfony ProgressBar, so you can run any method of this class. Alternatively, you can also use the `withSpinner` method by providing an iterable object.

```php
$this->withSpinner(User::all(), function($user) {
    // Do your stuff with $user
}, 'Loading...');
```