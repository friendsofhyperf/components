# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Overview

This is a **monorepo** containing 50+ production-ready components for the Hyperf PHP framework. Each component in `src/` is independently installable and can be split into its own repository.

- **PHP Version**: >=8.2
- **Framework**: Hyperf >=3.2.0
- **Testing Framework**: Pest (PHPUnit under the hood)
- **License**: MIT

## Development Commands

### Testing

```bash
# Run all tests (lint, unit, type coverage)
composer test

# Run unit tests only
composer test:unit
# Or directly: vendor/bin/pest

# Run specific test group
vendor/bin/pest --group=telescope
vendor/bin/pest --group=http-client

# Run linting (code style check)
composer test:lint

# Run type coverage analysis
composer test:types
```

### Code Quality

```bash
# Fix code style issues
composer cs-fix

# Run static analysis (PHPStan)
composer analyse

# Run type coverage analysis
composer analyse:types
```

### Component Management

```bash
# Normalize composer.json files across all components
composer json-fix

# Generate README.md for components
composer gen:readme

# Check for pending repository changes
composer repo:pending
```

## Architecture

### Monorepo Structure

Each component follows a standardized structure:

```
src/{component-name}/
├── composer.json           # Component-specific dependencies
├── LICENSE
├── README.md
└── src/
    ├── ConfigProvider.php  # Hyperf auto-discovery
    └── [component files]
```

### Configuration Providers

All components use Hyperf's `ConfigProvider` pattern for auto-discovery. When a component is installed, Hyperf automatically registers it via the `ConfigProvider` class. Key patterns:

- **Namespace Convention**: `FriendsOfHyperf\{ComponentName}`
- **ConfigProvider** defines: dependencies, commands, listeners, aspects, annotations
- **Publishing**: Components can publish config files and migrations to user applications

Example ConfigProvider structure:

```php
namespace FriendsOfHyperf\ComponentName;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],    // DI container bindings
            'commands' => [],        // Console commands
            'listeners' => [],       // Event listeners
            'aspects' => [],         // AOP aspects
            'annotations' => [       // Annotation scanning
                'scan' => [
                    'paths' => [__DIR__],
                ],
            ],
            'publish' => [],         // Publishable assets
        ];
    }
}
```

### Autoloading

The root `composer.json` declares PSR-4 autoloading for all components. Additionally, several components provide global helper functions through `files` autoload entries (see composer.json lines 224-239).

### Testing Structure

- Tests are organized in `tests/` directory, mirroring component structure
- Uses Pest testing framework with PHPUnit compatibility
- Test configuration in `tests/Pest.php` defines groups per component
- Run tests for specific components using `--group` flag

### Component Categories

The monorepo includes components across several domains:

- **Development Tools**: telescope, tinker, ide-helper, pretty-console
- **Database**: model-factory, model-observer, model-scope, fast-paginate, compoships
- **Caching/Storage**: cache, lock, redis-subscriber
- **HTTP/API**: http-client, oauth2-server
- **Notifications**: notification, notification-mail, notification-easysms, mail
- **Search**: elasticsearch, telescope-elasticsearch
- **Security**: encryption, purifier, recaptcha, validated-dto
- **Infrastructure**: confd, config-consul, facade, ipc-broadcaster

## Key Architectural Patterns

### Hyperf Integration

Components deeply integrate with Hyperf's:

- **Dependency Injection**: Use Hyperf's DI container for service registration
- **AOP (Aspect-Oriented Programming)**: Many components use aspects for cross-cutting concerns (e.g., Telescope)
- **Event System**: Components register listeners for framework events
- **Coroutines**: All code must be coroutine-safe (Swoole/Swow compatibility)

### Repository Splitting

Components are split into individual repositories for distribution. The `bin/split.sh` scripts handle this process. The root `composer.json` uses `replace` to indicate the monorepo provides all components.

### Documentation

- Multi-language docs in `docs/` using VitePress
- Primary language: Simplified Chinese (`docs/zh-cn/`)
- Translations: Traditional Chinese, Hong Kong Chinese, English
- Auto-translation scripts in `bin/`

## Important Notes

### Coroutine Safety

All code must be coroutine-safe. Avoid:

- Global state without proper context management
- Blocking I/O operations
- Non-coroutine-safe third-party libraries without wrappers

Use Hyperf's context API for request-scoped data:

```php
use Hyperf\Context\Context;

// Store in context
Context::set('key', $value);

// Retrieve from context
Context::get('key');
```

### Namespace Collisions

When adding new components, ensure namespace doesn't conflict with existing Hyperf components or other packages in the ecosystem.

### Testing Practices

- Use Pest's `uses()` helper to apply base test case
- Group tests by component using `->group()` modifier
- Mock Hyperf services appropriately (ValidatorFactory, ConfigInterface, etc.)
- Test files should end with `Test.php`

### Code Style

- Follows PSR-12 coding standards
- Uses PHP-CS-Fixer for automatic formatting (`.php-cs-fixer.php`)
- PHPStan at maximum level for static analysis
- All files include strict_types declaration
- File headers include license information

### Common Dependencies

Components commonly depend on:

- Hyperf framework components (e.g., hyperf/framework, hyperf/di, hyperf/command)
- Symfony components (console, http-foundation, process, var-dumper)
- Laravel utilities (serializable-closure)
- Carbon for date/time handling
- Guzzle for HTTP operations (via hyperf/guzzle)

## Running Single Tests

To run a single test file:

```bash
vendor/bin/pest tests/HttpClient/PendingRequestTest.php
```

To run a specific test method:

```bash
vendor/bin/pest --filter=test_method_name
```

## Working with Individual Components

To work on a specific component, navigate to its directory:

```bash
cd src/telescope
# Component has its own composer.json but shares root dependencies
```

The component's `composer.json` defines its specific dependencies and can be used for split repository publishing.
