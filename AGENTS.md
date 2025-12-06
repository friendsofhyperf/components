# Repository Guidelines

## Project Structure & Module Organization

- `src/`: Each component lives in its own subdirectory with PSR-4 namespaces matching
  `FriendsOfHyperf\*`; shared helper files sit alongside component code.
- `tests/`: Pest-based suites mirroring `src/` namespaces; add new coverage beside the
  component under test.
- `docs/` and `README*.md`: User-facing documentation; keep code snippets current when
  components change.
- `bin/`: Maintenance scripts (e.g., README regeneration, JSON normalization); run from
  repo root.
- `todos/`, `types/`, `vendor/`: Task notes, PHP stubs, and Composer installs. Avoid
  editing `vendor/` directly.

## Build, Test, and Development Commands

- Install PHP deps: `composer install` (use PHP ≥8.1 with required extensions enabled).
- Lint (dry run): `composer test:lint` for php-cs-fixer checks; auto-fix with
  `composer cs-fix -- path/to/file` when needed.
- Static analysis: `composer analyse` (PHPStan); stricter types run via
  `composer analyse:types`.
- Tests: `composer test` runs lint, unit, and type coverage; `composer test:unit` for
  Pest suites only; `composer test:types` for type-coverage plugin.
- Docs translation helper: `npm install` (or `pnpm install`) then
  `npm run docs:translate` if you need to regenerate multilingual docs.

## Coding Style & Naming Conventions

- Follow project PHP coding standard enforced by php-cs-fixer; prefer PSR-12 style,
  4-space indentation, and short array syntax.
- Namespaces mirror directory structure; keep component names kebab-case in path (e.g.,
  `src/pretty-console/src/`).
- Markdown: ATX headings, fenced code blocks with language hints, wrap lines near 100
  chars. JSON/TOML: 2-space indent; avoid comments in JSON.

## Testing Guidelines

- Use Pest for all new tests; place fixtures close to the test or component folder.
- Favor descriptive test names and data providers; keep unit tests deterministic (no
  network calls).
- Maintain type coverage by updating or adding tests when public APIs change; ensure
  `composer test:types` stays green before pushing.

## Commit & Pull Request Guidelines

- Conventional Commits required (e.g., `feat(cache): add redis tagging` or
  `fix(tinker): handle empty input`).
- PRs should include a short rationale, linked issues, before/after notes for behavior
  or docs, and confirmation that Composer scripts and linters pass.
- Document any config changes to `config.toml`/`config.json`; never include secrets.

## Security & Configuration Tips

- Keep `auth.json`, tokens, and personal `history.jsonl` local and out of commits; add
  or respect ignore rules as needed.
- Review logs and session artifacts before sharing to ensure PII/secrets are removed.
