# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **friendsofhyperf/components**, a monorepo containing popular PHP components for the Hyperf framework. It provides 40+ modular packages that extend Hyperf's capabilities with features like caching, mail, notifications, database enhancements, debugging tools, and more.

## Key Commands

### Testing & Quality Assurance

```bash
# Run all tests (includes lint, unit tests, and type coverage)
composer test

# Run unit tests only
composer test:unit

# Run linting (PHP-CS-Fixer dry run)
composer test:lint

# Run type coverage analysis
composer test:types

# Run PHPStan static analysis
composer analyse
```

### Code Formatting

```bash
# Fix code style issues
composer cs-fix

# Fix code style for specific file/directory
composer cs-fix src/cache/
```

### Development Utilities

```bash
# Fix all composer.json files and normalize them
composer json-fix

# Regenerate README files
composer gen:readme

# Manage repository components
composer repo:pending
```

## Architecture & Structure

### Monorepo Organization

- **`src/`** - Contains all component packages, each is a separate publishable package
- **`tests/`** - Centralized tests for all components using Pest testing framework
- **`docs/`** - Multi-language documentation (en, zh-cn, zh-hk, zh-tw)
- **`bin/`** - Development scripts for repository management and tooling

### Component Structure

Each component in `src/` follows this pattern:

- `composer.json` - Package definition and dependencies
- `src/` - Source code with PSR-4 autoloading
- `ConfigProvider.php` - Hyperf configuration provider
- Optional: `publish/`, `migrations/`, `resources/`

### Key Component Categories

- **Infrastructure**: cache, lock, ipc-broadcaster, tcp-sender
- **Database**: compoships, model-*, fast-paginate, mysql-grammar-addon
- **Messaging**: mail, notification, amqp-job, redis-subscriber
- **Development**: ide-helper, tinker, web-tinker, telescope, pest-plugin-hyperf
- **Utilities**: helpers, macros, facade, encryption, purifier
- **Integrations**: elasticsearch, sentry, openai-client, confd

### Namespace Convention

All components use `FriendsOfHyperf\{ComponentName}\` namespace pattern.

### Configuration

Components are auto-discoverable via Hyperf's ConfigProvider system. Each component registers itself in the main `composer.json` under `extra.hyperf.config`.

## Testing Strategy

- **Pest Framework**: Modern PHP testing framework used throughout
- **Centralized Tests**: All component tests are in `/tests` directory  
- **Test Structure**: Organized by component (e.g., `tests/Cache/`, `tests/Mail/`)
- **Type Coverage**: Enforced via Pest plugin for type safety

## Development Workflow

### Working with Individual Components

Each component can be developed independently but shares:

- Common dependencies in root `composer.json`
- Shared coding standards via `.php-cs-fixer.php`
- Unified testing via central `tests/` directory

### Code Standards

- PHP 8.1+ required
- PSR-4 autoloading
- Hyperf ~3.1.0 compatibility
- PHP-CS-Fixer for code formatting
- PHPStan Level 5 for static analysis

### Adding New Components

1. Create directory under `src/new-component/`
2. Add composer.json with proper namespace
3. Add ConfigProvider.php
4. Register in root composer.json autoload and hyperf config
5. Add tests in `tests/NewComponent/`

## Hyperf Integration

This project extends Hyperf framework capabilities. When working with components:

- Understand Hyperf's dependency injection system
- Use ConfigProvider pattern for service registration
- Follow Hyperf's annotation-based configuration where applicable
- Leverage Hyperf's event system for component integration

## Documentation

Components are documented in `docs/` with:

- English documentation in `docs/en/`
- Chinese variants in `docs/zh-cn/`, `docs/zh-hk/`, `docs/zh-tw/`
- Each component has its own markdown file explaining usage

## Release Management

The repository uses automated tooling for:

- Repository splitting for individual component releases
- Documentation generation and synchronization
- Dependency version management across components
