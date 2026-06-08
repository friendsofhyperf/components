# Co-PHPUnit

Co-PHPUnit runs PHPUnit tests inside a Swoole coroutine. It is useful for tests that exercise
coroutine-aware Hyperf components.

## Installation

Install the package as a development dependency:

```bash
composer require friendsofhyperf/co-phpunit --dev
```

The package requires `hyperf/coordinator` `~3.2.0` and supports PHPUnit 10, 11, and 12.
It does not declare the Swoole extension as a Composer dependency. Without the extension,
tests fall back to PHPUnit's normal execution.

## Usage

### Run Tests in Coroutines

Use `RunTestsInCoroutine` on a test class or on a shared base test class:

```php
<?php

namespace App\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class ServiceTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testRunsInCoroutine(): void
    {
        self::assertNotSame(-1, Coroutine::getCid());
    }
}
```

No additional PHPUnit or Composer autoload configuration is required after installing the
package.

### Opt Out of Coroutine Execution

Apply `NonCoroutine` to an individual test method to run that method without creating a
coroutine:

```php
<?php

namespace App\Tests;

use FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine;
use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class ServiceTest extends TestCase
{
    use RunTestsInCoroutine;

    #[NonCoroutine]
    public function testRunsWithoutCoroutine(): void
    {
        self::assertSame(-1, Coroutine::getCid());
    }
}
```

You can also apply `#[NonCoroutine]` to the test class to opt out every test method in that
class. The attribute targets classes and methods and has no constructor arguments.

These are the component's public APIs:

- `FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine`;
- `FriendsOfHyperf\CoPHPUnit\Attributes\NonCoroutine`.

## Execution Behavior

`RunTestsInCoroutine` overrides PHPUnit's `runBare()` method.

Before each test, it runs the test normally instead of creating a coroutine when any of these
conditions is true:

- the Swoole extension is not loaded;
- execution is already inside a Swoole coroutine;
- the test class has `#[NonCoroutine]`; or
- the current test method has `#[NonCoroutine]`.

Otherwise, it calls `Swoole\Coroutine\run()` and executes PHPUnit's parent `runBare()` inside
the coroutine. Exceptions are captured and rethrown after the coroutine exits. In a `finally`
block, the trait clears all Swoole timers and resumes Hyperf's `WORKER_EXIT` coordinator.
This cleanup only runs when the trait creates the coroutine.

`#[NonCoroutine]` prevents the trait from creating a coroutine. If the test is already running
inside a coroutine, the attribute does not move it out of that existing coroutine.

## PHPUnit Patch

The package registers `phpunit-patch.php` through Composer's `autoload.files`. When Composer's
autoload file is loaded, the patch locates PHPUnit's `TestCase` source file and removes the
`final` keyword from `TestCase::runBare()` if present, allowing the trait to override it.

This patch writes directly to the installed PHPUnit source file. Ensure the Composer vendor
directory is writable when the patch needs to be applied.
