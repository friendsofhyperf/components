# FriendsOfHyperf Components

[![Latest Test](https://github.com/friendsofhyperf/components/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/components/actions)
[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/components/v)](https://packagist.org/packages/friendsofhyperf/components)
[![License](https://poser.pugx.org/friendsofhyperf/components/license)](https://packagist.org/packages/friendsofhyperf/components)
[![PHP Version Require](https://poser.pugx.org/friendsofhyperf/components/require/php)](https://packagist.org/packages/friendsofhyperf/components)
[![Hyperf Version Require](https://img.shields.io/badge/hyperf->=3.1.0-brightgreen.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/components)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/friendsofhyperf/components)

[中文说明](README_CN.md)

🚀 The most popular and comprehensive collection of high-quality components for the [Hyperf](https://hyperf.io) framework, providing 50+ production-ready packages to accelerate your application development.

## 📖 About

This repository is a **monorepo** containing a collection of battle-tested, community-driven components that extend the Hyperf framework with additional features and integrations. Each component is independently usable and can be installed separately or as a complete suite.

## ✨ Features

- 🎯 **50+ Components** - Comprehensive collection covering various development needs
- 🔌 **Easy Integration** - Seamless integration with Hyperf 3.1+
- 📦 **Modular Design** - Install only what you need
- 🛡️ **Production Ready** - Battle-tested in production environments
- 📚 **Well Documented** - Comprehensive documentation in multiple languages
- 🧪 **Fully Tested** - High test coverage with PHPUnit and Pest
- 🌍 **Multi-language** - Documentation available in Chinese (Simplified, Traditional, HK) and English

## 📋 Requirements

- PHP >= 8.1
- Hyperf >= 3.1.0
- Swoole or Swow extension

## 💾 Installation

### Install All Components

```bash
composer require friendsofhyperf/components
```

### Install Individual Components

You can install specific components as needed:

```bash
# Example: Install Telescope (Debug Assistant)
composer require friendsofhyperf/telescope

# Example: Install HTTP Client
composer require friendsofhyperf/http-client

# Example: Install Model Factory
composer require friendsofhyperf/model-factory --dev
```

## 🎯 Quick Start

After installing a component, most packages will automatically register with Hyperf through the `ConfigProvider`. Some components may require publishing configuration files:

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/[component-name]
```

## 📦 Available Components

### 🔧 Development & Debugging Tools

- **[telescope](src/telescope)** - Elegant debug assistant for Hyperf (requests, exceptions, SQL, Redis, etc.)
- **[tinker](src/tinker)** - Powerful REPL for interactive debugging
- **[web-tinker](src/web-tinker)** - Web-based Tinker interface
- **[ide-helper](src/ide-helper)** - Enhanced IDE support and autocompletion
- **[pretty-console](src/pretty-console)** - Beautiful console output formatting

### 💾 Database & Models

- **[model-factory](src/model-factory)** - Database model factories for testing
- **[model-observer](src/model-observer)** - Eloquent model observers
- **[model-scope](src/model-scope)** - Global and local query scopes
- **[model-hashids](src/model-hashids)** - Hashids integration for models
- **[model-morph-addon](src/model-morph-addon)** - Polymorphic relationship enhancements
- **[compoships](src/compoships)** - Multi-column relationships for Eloquent
- **[fast-paginate](src/fast-paginate)** - High-performance pagination
- **[mysql-grammar-addon](src/mysql-grammar-addon)** - MySQL grammar extensions
- **[trigger](src/trigger)** - MySQL trigger support

### 🗄️ Caching & Storage

- **[cache](src/cache)** - Advanced caching with multiple drivers
- **[lock](src/lock)** - Distributed locking mechanisms
- **[redis-subscriber](src/redis-subscriber)** - Redis pub/sub subscriber

### 🌐 HTTP & API

- **[http-client](src/http-client)** - Elegant HTTP client (Laravel-style)
- **[oauth2-server](src/oauth2-server)** - OAuth2 server implementation

### 📨 Notifications & Communication

- **[notification](src/notification)** - Multi-channel notifications
- **[notification-mail](src/notification-mail)** - Email notification channel
- **[notification-easysms](src/notification-easysms)** - SMS notification via EasySMS
- **[mail](src/mail)** - Email sending component
- **[tcp-sender](src/tcp-sender)** - TCP message sender

### 🔍 Search & Data

- **[elasticsearch](src/elasticsearch)** - Elasticsearch client integration
- **[telescope-elasticsearch](src/telescope-elasticsearch)** - Elasticsearch storage for Telescope

### ⚙️ Configuration & Infrastructure

- **[confd](src/confd)** - Configuration management with confd
- **[config-consul](src/config-consul)** - Consul configuration center

### 🛠️ Command & Console

- **[command-signals](src/command-signals)** - Signal handling for commands
- **[command-validation](src/command-validation)** - Command input validation
- **[command-benchmark](src/command-benchmark)** - Command performance benchmarking
- **[console-spinner](src/console-spinner)** - Console loading spinners

### 🧩 Dependency Injection & Architecture

- **[facade](src/facade)** - Laravel-style facades for Hyperf
- **[ipc-broadcaster](src/ipc-broadcaster)** - Inter-process communication broadcaster

### 🔐 Security & Validation

- **[encryption](src/encryption)** - Data encryption and decryption
- **[purifier](src/purifier)** - HTML purification (XSS protection)
- **[recaptcha](src/recaptcha)** - Google reCAPTCHA integration
- **[validated-dto](src/validated-dto)** - Data Transfer Objects with validation
- **[grpc-validation](src/grpc-validation)** - gRPC request validation

### 🎨 Utilities & Helpers

- **[helpers](src/helpers)** - Useful helper functions
- **[support](src/support)** - Support utilities and classes
- **[macros](src/macros)** - Macro support for various classes

### 📊 Monitoring & Logging

- **[sentry](src/sentry)** - Sentry error tracking integration
- **[monolog-hook](src/monolog-hook)** - Monolog hooks and processors

### 🚀 Queue & Jobs

- **[amqp-job](src/amqp-job)** - AMQP-based job queue

### 🧪 Testing

- **[co-phpunit](src/co-phpunit)** - Coroutine-compatible PHPUnit

### 🤖 AI & External Services

- **[openai-client](src/openai-client)** - OpenAI API client

### 📝 Others

- **[exception-event](src/exception-event)** - Exception event handling

## 📚 Documentation

For detailed documentation, visit the [official documentation website](https://hyperf.fans/).

### Documentation by Language

- [简体中文 (Simplified Chinese)](https://hyperf.fans/zh-cn/)
- [繁體中文 (Traditional Chinese)](https://hyperf.fans/zh-tw/)
- [香港繁體 (Hong Kong Traditional)](https://hyperf.fans/zh-hk/)
- [English](https://hyperf.fans/en/)

## 🔨 Development

### Clone the Repository

```bash
git clone https://github.com/friendsofhyperf/components.git
cd components
```

### Install Dependencies

```bash
composer install
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suites
composer test:unit      # Unit tests
composer test:lint      # Code style checks
composer test:types     # Type coverage analysis
```

### Code Quality

```bash
# Fix code style
composer cs-fix

# Run static analysis
composer analyse
```

## 🤝 Contributing

We welcome contributions from the community! Please read our [Contributing Guidelines](CONTRIBUTE.md) before submitting pull requests.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests and code quality checks
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## 🌟 Support & Community

- 📖 **Documentation**: [hyperf.fans](https://hyperf.fans/)
- 💬 **Issues**: [GitHub Issues](https://github.com/friendsofhyperf/components/issues)
- 🐦 **Twitter**: [@huangdijia](https://twitter.com/huangdijia)
- 📧 **Email**: [huangdijia@gmail.com](mailto:huangdijia@gmail.com)

## 🔗 Mirrors

- [GitHub](https://github.com/friendsofhyperf/components)
- [CNB](https://cnb.cool/friendsofhyperf/components)

## 👥 Contributors

We are grateful to all the contributors who have helped make this project better!

[![Contributors](https://contrib.rocks/image?repo=friendsofhyperf/components)](https://github.com/friendsofhyperf/components/graphs/contributors)

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).

---

<p align="center">Made with ❤️ by <a href="https://github.com/huangdijia">Deeka Wong</a> and <a href="https://github.com/friendsofhyperf/components/graphs/contributors">contributors</a></p>
