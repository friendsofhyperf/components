# Repository Guidelines for AI Agents

## Scope and Source of Truth

- This repository is the source monorepo for 49 independently installable
  `friendsofhyperf/*` Composer packages.
- Treat component source, component `composer.json` files, and tests as authoritative.
  Do not invent public APIs, configuration keys, defaults, or framework behavior from
  documentation alone.
- Read the target component's `composer.json`, public source, tests found with `rg`, README,
  and published configuration before changing behavior or documentation.
- Preserve user changes and unrelated diffs. Do not revert, reformat, or regenerate files
  outside the requested scope.
- Do not create commits, push branches, split repositories, or publish releases unless the
  user explicitly requests it.

## Repository Model

- `src/<component>/`: independent kebab-case Composer packages with PSR-4 namespaces under
  `FriendsOfHyperf\*`.
- `tests/<Component>/`: shared Pest tests. Groups are registered in `tests/Pest.php`; not
  every component has a group.
- `docs/{en,zh-cn,zh-hk,zh-tw}/`: four synchronized VitePress locales. Simplified Chinese is
  the translation source.
- `types/`: PHP stubs checked independently at PHPStan max level.
- `bin/`: maintenance, generation, repository split, and release scripts.
- Root `composer.json`: generated aggregation of component dependencies, PSR-4 mappings,
  autoload files, ConfigProviders, and `replace` entries.

Most components expose a `src/ConfigProvider.php` that registers Hyperf dependencies,
listeners, aspects, commands, or publishable resources. Some components are framework-
independent libraries and intentionally have no ConfigProvider.

## Change Workflow

1. Inspect `git status --short` and identify the exact allowed write scope.
2. Read the target component's `composer.json`, relevant source, tests, README, and docs.
3. Search with `rg` for usages, contracts, configuration keys, and existing tests.
4. Make the smallest change consistent with local patterns.
5. Add or update focused tests for public behavior changes.
6. Update affected README and documentation snippets without adding unverified claims.
7. Run targeted verification first, then broader checks proportional to the change.
8. Finish with `git diff --check`, `git status --short`, and a path-scoped diff review.

## Build and Verification

Install PHP dependencies from the repository root:

```bash
composer install
```

Standard checks:

```bash
composer test          # lint, all Pest tests, then type coverage
composer analyse       # PHPStan; not included in composer test
composer analyse:types # PHPStan max-level analysis for types/
```

Focused checks:

```bash
vendor/bin/pest --group cache
vendor/bin/pest tests/CoPhpunit
vendor/bin/pest tests/Support/DispatchTest.php
composer analyse src/cache
composer cs-fix -- src/cache
```

- Prefer an existing Pest group from `tests/Pest.php`.
- When no group exists, run the component test directory or specific test file.
- Keep tests deterministic and offline. Use helpers in
  `tests/Concerns/InteractsWithContainer.php` for container mocks, swaps, and spies.
- `composer cs-fix` modifies files. Use it only on the intended paths and review its diff.
- Report checks that could not run because of missing PHP extensions, services, or tools.

## Component and Composer Rules

- Component directory names remain kebab-case; PHP classes remain StudlyCase and namespaces
  must match their PSR-4 mapping.
- Component-local dependencies belong in `src/<component>/composer.json`.
- Internal component dependencies should follow the repository's existing version
  constraints and package naming.
- Root aggregation metadata is maintained by `bin/composer-json-fixer`. If component package
  metadata changes, verify whether the root `composer.json` must be regenerated.
- `composer json-fix`, `composer repo:pending`, and `composer gen:readme` can modify many
  files. Do not run them for a narrowly scoped task unless their output is required.
- Never edit `vendor/` directly.

## Documentation Rules

- Component README files and `docs/<locale>/components/<component>.md` are separate sources;
  inspect both when behavior or examples change.
- Keep page sets and heading levels synchronized across `en`, `zh-cn`, `zh-hk`, and `zh-tw`.
- Keep code blocks, installation commands, configuration keys, and API examples
  semantically synchronized across all four locales.
- Every component documentation page must include the correct
  `composer require friendsofhyperf/<component>` command.
- When adding, removing, or renaming pages, update the four locale sidebars under
  `docs/.vitepress/src/<locale>/`.
- `docs/index.md` is the source for the Simplified Chinese home page workflow; do not update
  only `docs/zh-cn/index.md`.
- Automated translation can overwrite manually polished text. Review translations, links,
  Markdown structure, and terminology after generation.

Documentation checks:

```bash
npm install
npm run docs:check
```

`docs:check` validates locale page parity, heading structures, component installation
commands, and local links. It does not prove that prose or code examples match behavior, so
also verify claims against source and tests. `npm run docs:translate` modifies translated
files and requires translation service configuration; run it only when the user explicitly
requests translation regeneration.

### Sentry Documentation Notes

- `src/sentry/publish/sentry.php` is the source of truth for documented Sentry options,
  environment variables, default values, and publishable configuration examples.
- `src/sentry/src/ConfigProvider.php` is the source of truth for registered commands,
  listeners, aspects, dependencies, and the custom coroutine HTTP transport.
- Document Sentry annotations and helper functions only from public classes under
  `FriendsOfHyperf\Sentry\Annotation`, `FriendsOfHyperf\Sentry\Tracing\Annotation`,
  `FriendsOfHyperf\Sentry\Metrics\Annotation`, and `src/sentry/src/Function.php`.
- Keep the four Sentry component pages and both component READMEs semantically synchronized
  when updating logs, tracing, metrics, crons, transport, or optional dependency notes.

## Coding Style

- PHP follows PSR-12, uses 4-space indentation and short array syntax, and is enforced by
  php-cs-fixer.
- Preserve strict types declarations and existing component conventions.
- Markdown uses ATX headings and fenced code blocks with language hints. Keep prose concise
  and wrap long lines near 100 characters where practical.
- JSON uses 2-space indentation and no comments.
- Add abstractions only when they reduce real duplication or match an established pattern.

## Generated and High-Risk Operations

The following scripts are maintainer operations with broad or remote side effects:

- `bin/pending-repositories.sh`
- `bin/split.sh`, `bin/split-linux.sh`, and `bin/split-docs.sh`
- `bin/release.sh`

Split scripts can force-push independent repositories. Release scripts tag the monorepo and
component repositories. Never run these scripts unless the user explicitly requests the
operation and the remote impact is understood.

Do not expose or commit credentials such as `auth.json`, API keys, tokens, repository split
keys, or local session/history files. Scrub logs before sharing them.

## Commits and Pull Requests

- Use Conventional Commits with a component scope when applicable, for example
  `fix(cache): handle missing store`.
- Keep commits focused and do not include unrelated formatting or generated churn.
- Before a pull request, run the relevant focused tests, lint, PHPStan, and documentation
  checks. For broad behavior changes, run the complete applicable suite.
- Summaries should state the evidence inspected, behavior changed, checks run, checks not
  run, and exact files modified.
