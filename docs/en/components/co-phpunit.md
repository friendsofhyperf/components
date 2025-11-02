# Co-PHPUnit

A PHPUnit extension that enables tests to run inside Swoole coroutines, specifically designed for testing Hyperf applications and other Swoole-based frameworks.

## Installation

```bash
composer require friendsofhyperf/co-phpunit --dev
```

## Why Co-PHPUnit?

When testing Hyperf applications, many components rely on Swoole's coroutine context to function properly. Running tests in a traditional synchronous environment can lead to issues such as:

- Coroutine context not being available
- Timers and event loops not working correctly
- Coordinator pattern failures
- Database connection pool issues

Co-PHPUnit solves these problems by automatically wrapping test execution in a Swoole coroutine context when needed.

## Usage

### Basic Usage

Simply use the `RunTestsInCoroutine` trait in your test class:

```php
<?php

namespace Your\Namespace\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;

class YourTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testSomething()
    {
        // Your test code here
        // This will automatically run inside a Swoole coroutine
    }
}
```

### Disabling Coroutine for Specific Tests

If you need to disable coroutine execution for a specific test class, set the `$enableCoroutine` property to `false`:

```php
<?php

namespace Your\Namespace\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\TestCase;

class YourTest extends TestCase
{
    use RunTestsInCoroutine;

    protected bool $enableCoroutine = false;

    public function testSomething()
    {
        // This test will run in normal synchronous mode
    }
}
```

## How It Works

The `RunTestsInCoroutine` trait overrides PHPUnit's `runBare()` method to:

1. **Check Prerequisites**: Verifies that the Swoole extension is loaded and not already in a coroutine context
2. **Create Coroutine Context**: Wraps test execution in `Swoole\Coroutine\run()`
3. **Exception Handling**: Properly captures and re-throws exceptions from within the coroutine
4. **Cleanup**: Clears all timers and resumes coordinator on test completion
5. **Fallback**: If conditions aren't met, falls back to normal test execution

### PHPUnit Patch

The package includes a `phpunit-patch.php` file that automatically removes the `final` keyword from PHPUnit's `TestCase::runBare()` method, allowing the trait to override it. This patch is applied automatically when the package is autoloaded.

## Requirements

- PHP >= 8.0
- PHPUnit >= 10.0
- Swoole extension (when running tests in coroutine mode)
- Hyperf >= 3.1 (for coordinator functionality)

## Configuration

### Composer Autoload

The package automatically registers its autoload files in composer.json:

```json
{
    "autoload-dev": {
        "psr-4": {
            "Your\\Tests\\": "tests/"
        },
        "files": [
            "vendor/friendsofhyperf/co-phpunit/phpunit-patch.php"
        ]
    }
}
```

### PHPUnit Configuration

No special PHPUnit configuration is required. The package works seamlessly with your existing `phpunit.xml` configuration.

## Best Practices

1. **Use for Integration Tests**: This is particularly useful for integration tests that interact with Hyperf's coroutine-aware components
2. **Selective Enablement**: Not all tests need to run in coroutines. Use `$enableCoroutine = false` for unit tests that don't require coroutine context
3. **Test Isolation**: The package automatically cleans up timers and coordinator state between tests
4. **Performance**: Tests running in coroutines may have slightly different performance characteristics

## Example: Testing Hyperf Services

```php
<?php

namespace App\Tests;

use FriendsOfHyperf\CoPHPUnit\Concerns\RunTestsInCoroutine;
use Hyperf\Context\ApplicationContext;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    use RunTestsInCoroutine;

    public function testServiceWithCoroutineContext()
    {
        // Get service from container
        $service = ApplicationContext::getContainer()->get(YourService::class);

        // Test methods that use coroutine context
        $result = $service->asyncOperation();

        $this->assertNotNull($result);
    }

    public function testDatabaseConnection()
    {
        // Test database operations that require connection pool
        $result = Db::table('users')->first();

        $this->assertIsArray($result);
    }
}
```

## Troubleshooting

### Tests Hang or Timeout

If tests hang, ensure that:
- All async operations are properly awaited
- No infinite loops exist in coroutine callbacks
- Timers are cleared in test teardown

### "Call to a member function on null"

This usually indicates that coroutine context is not available. Ensure:
- Swoole extension is installed and enabled
- The `RunTestsInCoroutine` trait is included
- `$enableCoroutine` is set to `true`

### PHPUnit Version Compatibility

The package supports PHPUnit 10.x, 11.x, and 12.x. Ensure your PHPUnit version is compatible.
