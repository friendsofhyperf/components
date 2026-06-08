# Getting Started

FriendsOfHyperf Components is a monorepo of independently installable packages for Hyperf 3.2.
Use the full package when you want the complete toolkit, or install only the components your
application needs.

## Requirements

- PHP 8.2 or later
- Hyperf 3.2
- Swoole or Swow, according to your Hyperf application

## Install All Components

```shell
composer require friendsofhyperf/components
```

The aggregate package replaces every component package and registers their available
`ConfigProvider` classes.

## Install One Component

```shell
composer require friendsofhyperf/cache
```

Replace `cache` with a package name from the [component catalog](../components/index.md).
Most components register automatically through Hyperf's component discovery. When a component
provides configuration, publish it with the command documented on that component's page.

## Choose a Component

- Development and diagnostics: Telescope, Tinker, Web Tinker, IDE Helper
- Database and models: Model Factory, Model Observer, Compoships, Fast Paginate
- Infrastructure: Cache, Lock, Config Consul, Redis Subscriber
- Security and validation: Encryption, Purifier, reCAPTCHA, Validated DTO
- Communication: Mail, Notification, EasySms, AMQP Job, TCP Sender

See the [component catalog](../components/index.md) for the complete categorized list.

## Next Steps

1. Open the component page and review its requirements.
2. Install the individual package.
3. Publish configuration when the component requires it.
4. Add focused tests around the integration in your application.
