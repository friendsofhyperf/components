# console-spinner

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/console-spinner)](https://packagist.org/packages/friendsofhyperf/console-spinner)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/console-spinner)](https://packagist.org/packages/friendsofhyperf/console-spinner)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/console-spinner)](https://github.com/friendsofhyperf/console-spinner)

The progress bar component For Hyperf.

## Installation

```shell
composer require friendsofhyperf/console-spinner
```

## Publish

- Optional

```shell
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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
