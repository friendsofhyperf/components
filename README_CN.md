# FriendsOfHyperf 组件库

[![Latest Test](https://github.com/friendsofhyperf/components/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/components/actions)
[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/components/v)](https://packagist.org/packages/friendsofhyperf/components)
[![License](https://poser.pugx.org/friendsofhyperf/components/license)](https://packagist.org/packages/friendsofhyperf/components)
[![PHP Version Require](https://poser.pugx.org/friendsofhyperf/components/require/php)](https://packagist.org/packages/friendsofhyperf/components)
[![Hyperf Version Require](https://img.shields.io/badge/hyperf->=3.1.0-brightgreen.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/components)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/friendsofhyperf/components)

[English](README.md)

🚀 最受欢迎且全面的 [Hyperf](https://hyperf.io) 框架高质量组件集合，提供 50+ 个生产就绪的包，加速您的应用程序开发。

## 📖 关于

本仓库是一个 **单体仓库（monorepo）**，包含了一系列久经考验、社区驱动的组件，这些组件扩展了 Hyperf 框架的功能和集成。每个组件都可以独立使用，可以单独安装或作为完整套件安装。

## ✨ 特性

- 🎯 **50+ 组件** - 涵盖各种开发需求的全面集合
- 🔌 **易于集成** - 与 Hyperf 3.1+ 无缝集成
- 📦 **模块化设计** - 只安装您需要的组件
- 🛡️ **生产就绪** - 在生产环境中久经考验
- 📚 **文档完善** - 提供多语言的全面文档
- 🧪 **充分测试** - 使用 PHPUnit 和 Pest 进行高测试覆盖
- 🌍 **多语言支持** - 文档提供简体中文、繁体中文、香港繁体和英文版本

## 📋 环境要求

- PHP >= 8.1
- Hyperf >= 3.1.0
- Swoole 或 Swow 扩展

## 💾 安装

### 安装所有组件

```bash
composer require friendsofhyperf/components
```

### 安装单个组件

您可以根据需要安装特定组件：

```bash
# 示例：安装 Telescope（调试助手）
composer require friendsofhyperf/telescope

# 示例：安装 HTTP 客户端
composer require friendsofhyperf/http-client

# 示例：安装模型工厂
composer require friendsofhyperf/model-factory --dev
```

## 🎯 快速开始

安装组件后，大多数包会通过 `ConfigProvider` 自动注册到 Hyperf。部分组件可能需要发布配置文件：

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/[组件名称]
```

## 📦 可用组件

### 🔧 开发与调试工具

- **[telescope](src/telescope)** - 优雅的 Hyperf 调试助手（请求、异常、SQL、Redis 等）
- **[tinker](src/tinker)** - 强大的交互式调试 REPL
- **[web-tinker](src/web-tinker)** - 基于 Web 的 Tinker 界面
- **[ide-helper](src/ide-helper)** - 增强的 IDE 支持和自动补全
- **[pretty-console](src/pretty-console)** - 美化的控制台输出格式

### 💾 数据库与模型

- **[model-factory](src/model-factory)** - 用于测试的数据库模型工厂
- **[model-observer](src/model-observer)** - Eloquent 模型观察者
- **[model-scope](src/model-scope)** - 全局和局部查询作用域
- **[model-hashids](src/model-hashids)** - 模型的 Hashids 集成
- **[model-morph-addon](src/model-morph-addon)** - 多态关联增强
- **[compoships](src/compoships)** - Eloquent 的多列关联
- **[fast-paginate](src/fast-paginate)** - 高性能分页
- **[mysql-grammar-addon](src/mysql-grammar-addon)** - MySQL 语法扩展
- **[trigger](src/trigger)** - MySQL 触发器支持

### 🗄️ 缓存与存储

- **[cache](src/cache)** - 支持多驱动的高级缓存
- **[lock](src/lock)** - 分布式锁机制
- **[redis-subscriber](src/redis-subscriber)** - Redis 发布/订阅订阅者

### 🌐 HTTP 与 API

- **[http-client](src/http-client)** - 优雅的 HTTP 客户端（Laravel 风格）
- **[http-logger](src/http-logger)** - HTTP 请求/响应日志
- **[oauth2-server](src/oauth2-server)** - OAuth2 服务器实现

### 📨 通知与通信

- **[notification](src/notification)** - 多渠道通知
- **[notification-mail](src/notification-mail)** - 邮件通知渠道
- **[notification-easysms](src/notification-easysms)** - 通过 EasySMS 发送短信通知
- **[mail](src/mail)** - 邮件发送组件
- **[tcp-sender](src/tcp-sender)** - TCP 消息发送器

### 🔍 搜索与数据

- **[elasticsearch](src/elasticsearch)** - Elasticsearch 客户端集成
- **[telescope-elasticsearch](src/telescope-elasticsearch)** - Telescope 的 Elasticsearch 存储

### ⚙️ 配置与基础设施

- **[confd](src/confd)** - 使用 confd 进行配置管理
- **[config-consul](src/config-consul)** - Consul 配置中心

### 🛠️ 命令与控制台

- **[command-signals](src/command-signals)** - 命令的信号处理
- **[command-validation](src/command-validation)** - 命令输入验证
- **[command-benchmark](src/command-benchmark)** - 命令性能基准测试
- **[console-spinner](src/console-spinner)** - 控制台加载动画

### 🧩 依赖注入与架构

- **[di-plus](src/di-plus)** - 增强的依赖注入功能
- **[facade](src/facade)** - Hyperf 的 Laravel 风格门面
- **[middleware-plus](src/middleware-plus)** - 增强的中间件功能
- **[ipc-broadcaster](src/ipc-broadcaster)** - 进程间通信广播器

### 🔐 安全与验证

- **[encryption](src/encryption)** - 数据加密和解密
- **[purifier](src/purifier)** - HTML 净化（XSS 防护）
- **[recaptcha](src/recaptcha)** - Google reCAPTCHA 集成
- **[validated-dto](src/validated-dto)** - 带验证的数据传输对象
- **[grpc-validation](src/grpc-validation)** - gRPC 请求验证

### 🎨 实用工具与助手

- **[helpers](src/helpers)** - 实用的辅助函数
- **[support](src/support)** - 支持工具和类
- **[macros](src/macros)** - 各种类的宏支持

### 📊 监控与日志

- **[sentry](src/sentry)** - Sentry 错误追踪集成
- **[monolog-hook](src/monolog-hook)** - Monolog 钩子和处理器

### 🚀 队列与任务

- **[amqp-job](src/amqp-job)** - 基于 AMQP 的任务队列

### 🧪 测试

- **[pest-plugin-hyperf](src/pest-plugin-hyperf)** - Pest 测试框架集成
- **[co-phpunit](src/co-phpunit)** - 协程兼容的 PHPUnit

### 🤖 AI 与外部服务

- **[openai-client](src/openai-client)** - OpenAI API 客户端

### 📝 其他

- **[exception-event](src/exception-event)** - 异常事件处理

## 📚 文档

详细文档请访问 [官方文档网站](https://hyperf.fans/)。

### 多语言文档

- [简体中文](https://hyperf.fans/zh-cn/)
- [繁體中文](https://hyperf.fans/zh-tw/)
- [香港繁體](https://hyperf.fans/zh-hk/)
- [English](https://hyperf.fans/en/)

## 🔨 开发

### 克隆仓库

```bash
git clone https://github.com/friendsofhyperf/components.git
cd components
```

### 安装依赖

```bash
composer install
```

### 运行测试

```bash
# 运行所有测试
composer test

# 运行特定测试套件
composer test:unit      # 单元测试
composer test:lint      # 代码风格检查
composer test:types     # 类型覆盖率分析
```

### 代码质量

```bash
# 修复代码风格
composer cs-fix

# 运行静态分析
composer analyse
```

## 🤝 贡献

我们欢迎社区的贡献！在提交 Pull Request 之前，请阅读我们的[贡献指南](CONTRIBUTE.md)。

### 开发流程

1. Fork 本仓库
2. 创建特性分支（`git checkout -b feature/amazing-feature`）
3. 进行修改
4. 运行测试和代码质量检查
5. 提交更改（`git commit -m 'Add amazing feature'`）
6. 推送到分支（`git push origin feature/amazing-feature`）
7. 开启 Pull Request

## 🌟 支持与社区

- 📖 **文档**：[hyperf.fans](https://hyperf.fans/)
- 💬 **问题反馈**：[GitHub Issues](https://github.com/friendsofhyperf/components/issues)
- 🐦 **Twitter**：[@huangdijia](https://twitter.com/huangdijia)
- 📧 **邮箱**：[huangdijia@gmail.com](mailto:huangdijia@gmail.com)

## 🔗 镜像

- [GitHub](https://github.com/friendsofhyperf/components)
- [CNB](https://cnb.cool/friendsofhyperf/components)

## 👥 贡献者

感谢所有为本项目做出贡献的人！

[![Contributors](https://contrib.rocks/image?repo=friendsofhyperf/components)](https://github.com/friendsofhyperf/components/graphs/contributors)

## 📄 许可证

本项目采用 [MIT 许可证](LICENSE)开源。

---

<p align="center">由 <a href="https://github.com/huangdijia">Deeka Wong</a> 和<a href="https://github.com/friendsofhyperf/components/graphs/contributors">贡献者们</a>用 ❤️ 制作</p>
