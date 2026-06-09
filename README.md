# FriendsOfHyperf Components

[![Latest Test](https://github.com/friendsofhyperf/components/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/components/actions)
[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/components/v)](https://packagist.org/packages/friendsofhyperf/components)
[![License](https://poser.pugx.org/friendsofhyperf/components/license)](https://packagist.org/packages/friendsofhyperf/components)
[![PHP Version Require](https://poser.pugx.org/friendsofhyperf/components/require/php)](https://packagist.org/packages/friendsofhyperf/components)
[![Hyperf Version Require](https://img.shields.io/badge/hyperf-%3E%3D3.2.0-brightgreen.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/components)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/friendsofhyperf/components)

[中文说明](README_CN.md)

A monorepo of 48 independently installable components for
[Hyperf](https://hyperf.io) 3.2 and later.

## Requirements

- PHP 8.2 or later
- Hyperf 3.2 or later
- Swoole or Swow, when required by the application or component

Each component declares its exact dependencies in `src/<component>/composer.json`.

## Installation

Install the complete collection:

```bash
composer require friendsofhyperf/components
```

Or install only the components your application needs:

```bash
composer require friendsofhyperf/telescope
composer require friendsofhyperf/http-client
composer require friendsofhyperf/model-factory --dev
```

Most framework-integrated packages are discovered through their `ConfigProvider`. Some
components also publish configuration or resources:

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/<component>
```

Consult the component README and documentation before publishing files; not every component
provides publishable resources.

## Components

### Development and Debugging

- [telescope](src/telescope) - request, exception, SQL, Redis, and runtime inspection
- [tinker](src/tinker) - interactive REPL
- [web-tinker](src/web-tinker) - browser-based Tinker interface
- [ide-helper](src/ide-helper) - IDE metadata generation
- [pretty-console](src/pretty-console) - improved console presentation

### Database and Models

- [model-factory](src/model-factory), [model-observer](src/model-observer),
  [model-scope](src/model-scope), [model-hashids](src/model-hashids), and
  [model-morph-addon](src/model-morph-addon)
- [compoships](src/compoships), [fast-paginate](src/fast-paginate),
  [mysql-grammar-addon](src/mysql-grammar-addon), and [trigger](src/trigger)

### Infrastructure and Integrations

- Cache and coordination: [cache](src/cache), [lock](src/lock), and
  [redis-subscriber](src/redis-subscriber)
- HTTP and APIs: [http-client](src/http-client) and [oauth2-server](src/oauth2-server)
- Messaging: [notification](src/notification), [notification-mail](src/notification-mail),
  [notification-easysms](src/notification-easysms), [mail](src/mail), and
  [tcp-sender](src/tcp-sender)
- External services: [elasticsearch](src/elasticsearch),
  [telescope-elasticsearch](src/telescope-elasticsearch), [openai-client](src/openai-client),
  [recaptcha](src/recaptcha), and [sentry](src/sentry)
- Configuration: [confd](src/confd) and [config-consul](src/config-consul)

### Framework Extensions

- Commands: [command-benchmark](src/command-benchmark),
  [command-signals](src/command-signals), [command-validation](src/command-validation), and
  [console-spinner](src/console-spinner)
- Architecture: [facade](src/facade), [ipc-broadcaster](src/ipc-broadcaster), and
  [exception-event](src/exception-event)
- Security and validation: [encryption](src/encryption), [purifier](src/purifier),
  [rate-limit](src/rate-limit), [validated-dto](src/validated-dto), and
  [grpc-validation](src/grpc-validation)
- Shared utilities: [helpers](src/helpers), [support](src/support), and [macros](src/macros)
- Queue and testing: [amqp-job](src/amqp-job) and [co-phpunit](src/co-phpunit)

Every component directory contains its own package metadata and README. The complete list is
available under [`src/`](src).

## Documentation

The documentation site is available in four languages:

- [Simplified Chinese](https://docs.hdj.me/zh-cn/)
- [Traditional Chinese](https://docs.hdj.me/zh-tw/)
- [Hong Kong Traditional Chinese](https://docs.hdj.me/zh-hk/)
- [English](https://docs.hdj.me/en/)

Component documentation is maintained under `docs/<locale>/components/`. Component READMEs
and documentation pages are separate sources, so behavior changes may require updates to
both.

## Repository Layout

```text
src/<component>/        independently installable component packages
tests/<Component>/      shared Pest test suite
docs/<locale>/          VitePress documentation in four languages
types/                  PHP stubs checked at PHPStan max level
bin/                    repository maintenance, split, and release scripts
```

The root package aggregates all components. Most components integrate with Hyperf through a
`ConfigProvider`; a small number are framework-independent libraries.

## Development

Install dependencies from the repository root:

```bash
composer install
```

Run the standard local checks:

```bash
composer test          # code style, Pest tests, and type coverage
composer analyse       # PHPStan analysis
composer analyse:types # PHPStan max-level analysis for types/
```

Run focused checks while developing a component:

```bash
vendor/bin/pest --group cache
vendor/bin/pest tests/CoPhpunit
composer analyse src/cache
composer cs-fix -- src/cache
```

Pest groups are defined in [`tests/Pest.php`](tests/Pest.php). Not every component has a
group, so use a test directory or file when necessary. `composer test` does not run PHPStan;
run `composer analyse` separately before submitting code changes.

For documentation changes:

```bash
npm install
npm run docs:check
```

Simplified Chinese is the translation source. Keep the page set and heading structure
synchronized across `en`, `zh-cn`, `zh-hk`, and `zh-tw`, then review generated translations
before submission. `npm run docs:translate` modifies translated files and requires translation
service configuration; run it only when intentionally regenerating translations.

## Contributing

Read [CONTRIBUTE.md](CONTRIBUTE.md) and [AGENTS.md](AGENTS.md) before making changes.

- Treat component source and tests as the authority for documented behavior.
- Update tests when public behavior changes.
- Update the component README and documentation snippets when they are affected.
- Keep changes scoped; do not edit generated or unrelated files without a reason.
- Run targeted checks first, followed by the relevant repository-level checks.
- Use Conventional Commits, scoped to a component when possible.

## Release Model

This monorepo is the source of truth. Maintenance workflows split `src/<component>` into
individual `friendsofhyperf/<component>` repositories, and releases apply a shared version
tag across the monorepo and component repositories.

The split and release scripts force-push or tag remote repositories. They are maintainer-only
operations and should not be run during normal development.

## Community

- [Documentation](https://docs.hdj.me/)
- [GitHub Issues](https://github.com/friendsofhyperf/components/issues)
- [CNB Mirror](https://cnb.cool/friendsofhyperf/components)
- [Contributors](https://github.com/friendsofhyperf/components/graphs/contributors)

## License

FriendsOfHyperf Components is open-sourced software licensed under the [MIT License](LICENSE).
