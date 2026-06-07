# Frequently Asked Questions

## Where should I report a problem?

Open an issue in the
[components monorepo](https://github.com/friendsofhyperf/components/issues). Include the component
name, installed versions, a minimal reproduction, and the full exception trace.

## Should I install the aggregate package or an individual component?

Install `friendsofhyperf/components` when you intentionally need the complete collection. For most
applications, install individual `friendsofhyperf/*` packages to keep dependencies and enabled
providers focused.

## Do components register automatically?

Most components expose a Hyperf `ConfigProvider` and register through component discovery.
Components that require configuration or database migrations document their publish commands on
their component page.

## Which versions are supported?

The current branch requires PHP 8.2 or later and targets Hyperf 3.2. Check the selected component's
`composer.json` for its exact required and suggested dependencies.

## Why does an example require another package?

Some features integrate with optional packages such as AMQP, Kafka, async queue, Elasticsearch, or
external services. Composer's `suggest` section and the component documentation identify those
optional dependencies.

## Where should I submit a pull request?

Submit all pull requests to the
[components monorepo](https://github.com/friendsofhyperf/components). The individual component
repositories are generated splits and are read-only for contributions.

Read [About FriendsOfHyperf](about.md) and [How to use components](how-to-use.md) for more detail.
