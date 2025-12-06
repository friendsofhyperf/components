# GitHub Copilot Instructions for FriendsOfHyperf Components

This repository contains a monorepo of popular components for the Hyperf PHP framework. This document provides guidance for GitHub Copilot to better understand the project structure and conventions.

> **Note**: Also see `CLAUDE.md` and `AGENTS.md` in the repository root for additional development guidance.

## Project Overview

- **Repository**: friendsofhyperf/components
- **Type**: Monorepo containing 49 Hyperf components
- **PHP Version**: >=8.2
- **Framework**: Hyperf >=3.2.0
- **Testing Framework**: Pest (PHPUnit under the hood)
- **License**: MIT
- **Main Author**: Deeka Wong (huangdijia@gmail.com)

## Repository Structure

```
/
├── bin/                    # Utility scripts
├── docs/                   # Multi-language documentation (VitePress)
├── src/                    # Component source code (49 components)
├── tests/                  # Test suites (Pest)
├── types/                  # PHP type stubs
├── .github/                # GitHub workflows and configuration
├── composer.json           # Main composer configuration
├── package.json            # Documentation build configuration
├── phpstan.neon.dist       # PHPStan configuration
├── CLAUDE.md               # Claude Code (claude.ai/code) development guidance
└── AGENTS.md               # Repository guidelines for agents
```

## Component Architecture

Each component in `src/` follows this structure:
```
src/{component-name}/
├── .gitattributes
├── .github/                # Component-specific GitHub config
├── LICENSE
├── README.md
├── composer.json           # Component-specific dependencies
└── src/                    # Component source code
    ├── ConfigProvider.php  # Hyperf configuration provider
    └── [component files]
```

### Key Components Include:

**Development Tools**:
- **telescope**: Application debugging and monitoring
- **tinker**: Interactive REPL
- **ide-helper**: IDE autocompletion helper
- **pretty-console**: Enhanced console output

**Database**:
- **model-factory**: Database model factories
- **model-observer**: Model event observers
- **model-scope**: Model query scopes
- **fast-paginate**: Optimized pagination
- **compoships**: Composite key relationships
- **mysql-grammar-addon**: MySQL grammar extensions

**Caching/Storage**:
- **cache**: Enhanced caching functionality
- **lock**: Distributed locking
- **redis-subscriber**: Redis pub/sub subscriber

**HTTP/API**:
- **http-client**: HTTP client utilities
- **oauth2-server**: OAuth2 server implementation
- **openai-client**: OpenAI API client

**Notifications**:
- **notification**: Multi-channel notifications
- **notification-mail**: Email notifications
- **notification-easysms**: SMS notifications via EasySms
- **mail**: Mail sending functionality

**Search**:
- **elasticsearch**: Elasticsearch integration
- **telescope-elasticsearch**: Telescope Elasticsearch storage

**Security**:
- **encryption**: Encryption utilities
- **purifier**: HTML purification
- **recaptcha**: Google reCAPTCHA integration
- **validated-dto**: Data Transfer Objects with validation

**Infrastructure**:
- **confd**: Configuration daemon
- **config-consul**: Consul configuration center
- **facade**: Laravel-style facades
- **ipc-broadcaster**: IPC broadcasting
- **sentry**: Sentry error tracking integration

## Development Conventions

### Namespace Convention
All components use the namespace pattern: `FriendsOfHyperf\{ComponentName}`

### Configuration Providers
Each component typically includes a `ConfigProvider.php` file that defines:
- Dependencies
- Commands
- Listeners
- Annotations

### Code Style
- Follows PSR-12 coding standards
- Uses PHP-CS-Fixer for code formatting (`.php-cs-fixer.php`)
- PHPStan for static analysis (`phpstan.neon.dist`)

### Testing
- Uses Pest testing framework (PHPUnit under the hood)
- Test configuration in `phpunit.xml.dist` and `tests/Pest.php`
- Test files located in `tests/` directory with groups per component
- Follows Hyperf testing patterns
- Type coverage analysis via Pest plugin

## Documentation Structure

Documentation is available in multiple languages:
- `docs/zh-cn/` - Simplified Chinese (primary)
- `docs/zh-tw/` - Traditional Chinese
- `docs/zh-hk/` - Hong Kong Chinese  
- `docs/en/` - English

### Documentation Generation
- Uses VitePress for documentation site
- Configuration in `docs/.vitepress/config.mts`
- Auto-generated component docs via `bin/generate-repository-doc.sh`
- Translation scripts available in `bin/`

## Build and Deployment

### Scripts Available:
- `bin/generate-repository-doc.sh` - Generate component documentation
- `bin/regenerate-readme.sh` - Regenerate README files
- `bin/doc-translate` - PHP-based translation script (uses OpenCC)
- `bin/doc-translate.js` - JavaScript-based translation
- `bin/doc-translate.github-model.js` - AI-powered translation using language models
- `bin/split-docs.sh` - Deploy documentation to separate repository
- `bin/split.sh`, `bin/split-linux.sh` - Split components to individual repos
- `bin/release.sh` - Release management
- `bin/composer-json-fixer` - Fix and normalize composer.json files
- `bin/pending-repositories.sh` - Check for pending repository changes

### GitHub Actions:
- `tests.yaml` - Run main test suite (CS Fixer, PHPStan, Pest)
- `test-components.yml` - Component-specific tests (PHP MySQL Replication, Carbon, Symfony, Elasticsearch)
- `docs-split.yaml` - Deploy documentation to separate repository
- `docs-translate.yaml` - Auto-translate documentation
- `release.yaml` - Handle releases
- `split.yaml` - Split components to individual repositories
- `update-changelog.yml` - Auto-update changelog on releases
- `cnb.yaml` - Mirror to CNB repository

## Development Workflow

### Adding New Components:
1. Create directory in `src/{component-name}/`
2. Follow the standard component structure
3. Add component to documentation sidebars
4. Create tests in `tests/` directory
5. Update main `composer.json` if needed

### Documentation Updates:
1. Edit primary documentation in `docs/zh-cn/`
2. Run translation scripts to update other languages
3. Update navigation in `.vitepress/src/{lang}/nav.ts`
4. Update sidebars in `.vitepress/src/{lang}/sidebars.ts`

## Component Development Patterns

### Configuration Provider Template:
```php
<?php

declare(strict_types=1);

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

### Typical Component composer.json:
```json
{
    "name": "friendsofhyperf/component-name",
    "description": "Component description",
    "license": "MIT",
    "require": {
        "php": ">=8.2",
        "hyperf/framework": "~3.2.0"
    },
    "autoload": {
        "psr-4": {
            "FriendsOfHyperf\\ComponentName\\": "src/"
        }
    },
    "extra": {
        "hyperf": {
            "config": "FriendsOfHyperf\\ComponentName\\ConfigProvider"
        }
    }
}
```

## Common Dependencies

The project commonly uses:
- Hyperf framework components
- Symfony components for console, HTTP foundation
- Laravel components for certain utilities
- Guzzle for HTTP clients
- Carbon for date/time handling

## Installation Instructions

Components can be installed individually:
```bash
composer require friendsofhyperf/{component-name}
```

Or install the entire suite:
```bash
composer require friendsofhyperf/components
```

## Testing Conventions

- Test files should be in `tests/` directory, mirroring component structure
- Uses Pest testing framework with PHPUnit compatibility
- Test groups configured in `tests/Pest.php` per component
- Run tests for specific components using `--group` flag
- Use Hyperf testing utilities
- Mock external dependencies appropriately using `tests/Concerns/InteractsWithContainer.php`
- Follow AAA pattern (Arrange, Act, Assert)
- Maintain type coverage; add/update tests when public APIs change

## Documentation Standards

- All components should have comprehensive README.md
- Documentation should include installation and usage examples
- Chinese documentation is primary, with translations to other languages
- Code examples should be practical and working

## Contributing Guidelines

1. Fork the repository
2. Create feature branch from main
3. Follow coding standards (PHP-CS-Fixer, PHPStan)
4. Write/update tests
5. Update documentation
6. Submit pull request

### Commit Guidelines
- Use Conventional Commits, scoped where possible (e.g., `feat(cache): add redis tagging`, `fix(tinker): handle empty input`)
- Before opening a PR, ensure `composer test` passes
- Include rationale, linked issues, and before/after notes for behavioral changes
- Never commit secrets or sensitive data

## Coroutine Safety

All code must be coroutine-safe (Swoole/Swow compatibility). Avoid:
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

## Deployment and Release

- Components are split into individual repositories
- Documentation is deployed to separate GitHub Pages site
- Releases are managed through GitHub Actions
- Semantic versioning is used

When working with this codebase, prioritize:
1. Consistency with existing patterns
2. Proper Hyperf integration
3. Comprehensive documentation
4. Test coverage
5. Performance considerations for framework components

## Working with the Codebase

### Tool Calling
You have the capability to call multiple tools in a single response. For maximum efficiency, whenever you need to perform multiple independent operations, ALWAYS call tools simultaneously whenever the actions can be done in parallel rather than sequentially.
Especially when exploring repository, searching, reading files, viewing directories, validating changes, reporting progress or replying to comments. For Example you can read 3 different files parallelly, or report progress and edit different files in parallel. Always report progress in parallel with other tool calls that follow it as it does not depend on the result of those calls.
However, if some tool calls depend on previous calls to inform dependent values like the parameters, do NOT call these tools in parallel and instead call them sequentially.

### Code Modification Principles
- **Make minimal changes**: Only modify what's necessary to achieve the goal
- **Preserve working code**: Never delete or modify working code unless absolutely required
- **Surgical precision**: Change as few lines as possible
- **Validate before committing**: Always test changes before reporting progress
- **Use existing tools**: Leverage composer, npm, and other ecosystem tools rather than manual changes

### Testing Strategy
- Run existing tests before making changes to understand baseline
- Create focused tests for new functionality
- Use Pest following Hyperf testing patterns
- Mock external dependencies appropriately
- Run tests iteratively after each change
- Don't remove or edit unrelated tests
- Use `--group` flag to run component-specific tests

### Error Handling
- Check for and handle PHP errors appropriately
- Use proper exception handling following Hyperf conventions
- Log errors using Hyperf's logging system
- Provide meaningful error messages
- Don't suppress errors without good reason

### File Operations
- Always use absolute paths when referring to repository files
- Use `view` to examine files before modifying
- Use `str_replace` for precise, targeted edits
- Create new files only when necessary
- Never recreate existing files (use `str_replace` instead)
- Use `.gitignore` to exclude build artifacts and dependencies

### Build and Test Commands
Common commands for this repository:
- `composer install` - Install PHP dependencies
- `composer test` - Run all tests (lint, unit, types)
- `composer test:unit` - Run unit tests only (vendor/bin/pest)
- `composer test:lint` - Run linting (dry-run)
- `composer test:types` - Run type coverage analysis
- `composer cs-fix` - Run PHP-CS-Fixer
- `composer analyse` - Run PHPStan analysis
- `composer analyse:types` - Run stricter type analysis
- `composer json-fix` - Normalize all composer.json files
- `composer gen:readme` - Generate README files
- `vendor/bin/pest --group=<component>` - Run tests for specific component
- `vendor/bin/pest --parallel` - Run tests in parallel
- `cd docs && pnpm install` - Install documentation dependencies
- `cd docs && pnpm run docs:dev` - Run documentation dev server
- `cd docs && pnpm run docs:build` - Build documentation
- `pnpm run docs:translate` - Translate documentation

### Reporting Progress
- Use `report_progress` early to outline your plan
- Report progress after completing meaningful units of work
- Update the checklist to show progress
- Review committed files to ensure scope is minimal
- Commit messages should be clear and concise