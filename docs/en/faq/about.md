# About FriendsOfHyperf

## What is FriendsOfHyperf?

FriendsOfHyperf is a community project that maintains reusable components for Hyperf. The
collection covers development tools, database helpers, messaging, validation, monitoring, and
external service integrations.

## How is the repository organized?

The `components` repository is the source of truth. Each directory under `src/` contains an
independently installable Composer package, while `friendsofhyperf/components` installs the
complete collection.

## How are packages released?

The monorepo is split into individual read-only package repositories during the release process.
Issues and pull requests belong in the monorepo.

## How is documentation maintained?

The documentation site uses VitePress and maintains matching pages in four locales. Run
`npm run docs:check` from the repository root before submitting documentation changes.
