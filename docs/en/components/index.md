# Components

Every component can be installed independently with
`composer require friendsofhyperf/<component>`. Install
`friendsofhyperf/components` only when you need the complete collection.

## Development and Diagnostics

- [Telescope](telescope.md): inspect requests, exceptions, queries, Redis commands, and more.
- [Tinker](tinker.md): run an interactive REPL in a Hyperf application.
- [Web Tinker](web-tinker.md): expose Tinker through a browser interface.
- [IDE Helper](ide-helper.md): generate IDE metadata for application classes.
- [Pretty Console](pretty-console.md): format command-line output.
- [Command Benchmark](command-benchmark.md): measure command execution.

## Database and Models

- [Model Factory](model-factory.md): create model factories for tests and seed data.
- [Model Observer](model-observer.md): register model observers.
- [Model Scope](model-scope.md): define reusable model query scopes.
- [Model Hashids](model-hashids.md): expose encoded model identifiers.
- [Model Morph Addon](model-morph-addon.md): extend polymorphic model relations.
- [Compoships](compoships.md): define relationships using multiple columns.
- [Fast Paginate](fast-paginate.md): optimize pagination for large data sets.
- [MySQL Grammar Addon](mysql-grammar-addon.md): extend the MySQL query grammar.
- [Trigger](trigger.md): consume MySQL binlog events.

## Cache and Coordination

- [Cache](cache.md): use an expressive cache API.
- [Lock](lock.md): coordinate work with distributed locks.
- [Redis Subscriber](redis-subscriber.md): consume Redis Pub/Sub messages.

## HTTP and External Services

- [HTTP Client](http-client.md): use a convenient HTTP client.
- [OAuth2 Server](oauth2-server.md): build an OAuth 2.0 authorization server.
- [OpenAI Client](openai-client.md): integrate the OpenAI PHP client.
- [Elasticsearch](elasticsearch.md): configure the Elasticsearch client.
- [reCAPTCHA](recaptcha.md): verify Google reCAPTCHA responses.

## Messaging and Notifications

- [AMQP Job](amqp-job.md): dispatch AMQP messages as jobs.
- [Mail](mail.md): send mail with Symfony Mailer.
- [Notification](notification.md): deliver notifications through multiple channels.
- [Notification Mail](notification-mail.md): deliver notifications by email.
- [Notification EasySms](notification-easysms.md): deliver notifications by SMS.
- [TCP Sender](tcp-sender.md): send messages to TCP services.

## Configuration and Infrastructure

- [Confd](confd.md): manage configuration with confd.
- [Config Consul](config-consul.md): load configuration from Consul.
- [IPC Broadcaster](ipc-broadcaster.md): broadcast messages between worker processes.
- [Telescope Elasticsearch](telescope-elasticsearch.md): store Telescope entries in Elasticsearch.

## Security and Validation

- [Encryption](encryption.md): encrypt and decrypt application data.
- [Purifier](purifier.md): sanitize HTML input.
- [Validated DTO](validated-dto.md): validate and hydrate data transfer objects.
- [Command Validation](command-validation.md): validate console command input.
- [gRPC Validation](grpc-validation.md): validate gRPC requests.
- [Rate Limit](rate-limit.md): apply configurable rate-limiting algorithms.

## Framework Extensions

- [Facade](facade.md): define Laravel-style facades.
- [Macros](macros.md): add macros to framework classes.
- [Helpers](helpers.md): use additional helper functions.
- [Support](support.md): use shared utilities, fluent dispatch, and backoff strategies.
- [Exception Event](exception-event.md): dispatch events for exceptions.

## Commands and Runtime

- [Command Signals](command-signals.md): handle process signals in commands.
- [Console Spinner](console-spinner.md): display progress spinners.
- [Co-PHPUnit](co-phpunit.md): run PHPUnit tests in coroutines.
