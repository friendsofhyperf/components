# Once

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/once/version.png)](https://packagist.org/packages/friendsofhyperf/once)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/once/d/total.png)](https://packagist.org/packages/friendsofhyperf/once)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/once)](https://github.com/friendsofhyperf/once)

A magic memoization function for Hyperf.

## Installation

- Installation

```bash
composer require friendsofhyperf/once
```

## Documentation

- [Documentation](https://github.com/spatie/once)

## Usage

```php
use FriendsOfHyperf\Once\Annotation\Forget;
use FriendsOfHyperf\Once\Annotation\Once;

class Foo
{
    #[Once]
    public function getNumber(): int
    {
        return rand(1, 10000);
    }

    #[Forget]
    public function forgetNumber()
    {
    }
}
```
