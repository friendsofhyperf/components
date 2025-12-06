# Repository Guidelines

## Project Structure & Module Organization

- `src/`: Hyperf components, each in a kebab-case folder (e.g., `src/pretty-console/src/`) with PSR-4 namespaces `FriendsOfHyperf\*`.
- `tests/`: Pest test suites mirroring `src/` namespaces; grouped execution configured in `tests/Pest.php`.
- `docs/`, `README*.md`: User-facing docs; update snippets when component behavior changes.
- `bin/`: Maintenance helpers (README regeneration, JSON fixer). Run from repo root.
- `todos/`, `types/`: Task notes and PHP stubs. Avoid editing `vendor/` directly.

## Build, Test, and Development Commands

- Install PHP deps: `composer install` (PHP ≥ 8.2 with required extensions).
- Lint (dry run): `composer test:lint` uses php-cs-fixer; auto-fix with
  `composer cs-fix -- path/to/file`.
- Static analysis: `composer analyse` (PHPStan); stricter run `composer analyse:types`.
- Tests: `composer test` runs lint → unit → type coverage; `composer test:unit` or
  `vendor/bin/pest` for unit-only; `composer test:types` to enforce type coverage.
- Targeted groups: `vendor/bin/pest --group cache`, `--group telescope`, etc.
- Docs translation helper: `npm install` (or `pnpm install`), then `npm run docs:translate`.

## Coding Style & Naming Conventions

- PHP: PSR-12 style, 4-space indentation, short array syntax; enforced by php-cs-fixer.
- Namespaces align with directory structure; keep component folders kebab-case and class
  names StudlyCase.
- Markdown: ATX headings, fenced code blocks with language hints, wrap lines around
  100 chars. JSON/TOML: 2-space indent; avoid comments in JSON.

## Testing Guidelines

- Framework: Pest with base case `FriendsOfHyperf\Tests\TestCase` (sets up Hyperf DI and
  mixin listeners). Fixtures live near the specs they serve.
- Keep tests deterministic (no external network); mock collaborators via helpers in
  `tests/Concerns/InteractsWithContainer.php`.
- Maintain type coverage; add/update tests when public APIs or DTO contracts change.

## Commit & Pull Request Guidelines

- Use Conventional Commits, scoped where possible (e.g., `feat(cache): add redis tagging`,
  `fix(tinker): handle empty input`).
- Before opening a PR, ensure `composer test` passes; include rationale, linked issues,
  and before/after notes for behavioral or doc changes.
- Document any config updates to `config.toml`/`config.json`; never commit secrets.

## Security & Configuration Tips

- Keep `auth.json`, tokens, `history.jsonl`, and `sessions/` artifacts local; add ignore
  rules as needed.
- Scrub logs or debug output before sharing to avoid leaking PII or credentials.
