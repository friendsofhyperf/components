# FriendsOfHyperf Components

[![Latest Test](https://github.com/friendsofhyperf/components/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/components/actions)
[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/components/v)](https://packagist.org/packages/friendsofhyperf/components)
[![License](https://poser.pugx.org/friendsofhyperf/components/license)](https://packagist.org/packages/friendsofhyperf/components)
[![PHP Version Require](https://poser.pugx.org/friendsofhyperf/components/require/php)](https://packagist.org/packages/friendsofhyperf/components)
[![Hyperf Version Require](https://img.shields.io/badge/hyperf-%3E%3D3.2.0-brightgreen.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/components)
[![OpenSSF Scorecard](https://api.scorecard.dev/projects/github.com/friendsofhyperf/components/badge)](https://scorecard.dev/viewer/?uri=github.com/friendsofhyperf/components)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/friendsofhyperf/components)

[English](README.md)

面向 [Hyperf](https://hyperf.io) 3.2 及以上版本的组件 monorepo，包含 49 个可独立安装的组件。

## 环境要求

- PHP 8.2 或以上版本
- Hyperf 3.2 或以上版本
- 应用或组件需要时，安装 Swoole 或 Swow

每个组件的准确依赖声明位于 `src/<component>/composer.json`。

## 安装

安装完整组件集合：

```bash
composer require friendsofhyperf/components
```

也可以只安装应用需要的组件：

```bash
composer require friendsofhyperf/telescope
composer require friendsofhyperf/http-client
composer require friendsofhyperf/model-factory --dev
```

大部分与框架集成的组件会通过 `ConfigProvider` 自动发现。部分组件还提供配置或资源发布：

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/<component>
```

并非所有组件都提供可发布资源，执行前请先查看组件 README 和文档。

## 组件

### 开发与调试

- [telescope](src/telescope) - 查看请求、异常、SQL、Redis 和运行时信息
- [tinker](src/tinker) - 交互式 REPL
- [web-tinker](src/web-tinker) - 基于浏览器的 Tinker 界面
- [ide-helper](src/ide-helper) - 生成 IDE 元数据
- [pretty-console](src/pretty-console) - 改进控制台输出

### 数据库与模型

- [model-factory](src/model-factory)、[model-observer](src/model-observer)、
  [model-scope](src/model-scope)、[model-hashids](src/model-hashids) 和
  [model-morph-addon](src/model-morph-addon)
- [compoships](src/compoships)、[fast-paginate](src/fast-paginate)、
  [mysql-grammar-addon](src/mysql-grammar-addon) 和 [trigger](src/trigger)

### 基础设施与集成

- 缓存与协调：[cache](src/cache)、[lock](src/lock) 和
  [redis-subscriber](src/redis-subscriber)
- HTTP 与 API：[http-client](src/http-client) 和 [oauth2-server](src/oauth2-server)
- 消息与通知：[notification](src/notification)、[notification-mail](src/notification-mail)、
  [notification-easysms](src/notification-easysms)、[mail](src/mail) 和
  [tcp-sender](src/tcp-sender)
- 外部服务与可观测性：[elasticsearch](src/elasticsearch)、
  [telescope-elasticsearch](src/telescope-elasticsearch)、[openai-client](src/openai-client)、
  [recaptcha](src/recaptcha) 和 [sentry](src/sentry)
- 配置管理：[confd](src/confd) 和 [config-consul](src/config-consul)

### 框架扩展

- 命令行：[command-benchmark](src/command-benchmark)、
  [command-signals](src/command-signals)、[command-validation](src/command-validation) 和
  [console-spinner](src/console-spinner)
- 架构能力：[facade](src/facade)、[ipc-broadcaster](src/ipc-broadcaster) 和
  [exception-event](src/exception-event)
- 安全与验证：[encryption](src/encryption)、[purifier](src/purifier)、
  [rate-limit](src/rate-limit)、[validated-dto](src/validated-dto) 和
  [grpc-validation](src/grpc-validation)
- 通用工具：[helpers](src/helpers)、[support](src/support) 和 [macros](src/macros)
- 队列与测试：[amqp-job](src/amqp-job) 和 [co-phpunit](src/co-phpunit)

每个组件目录均包含独立的包元数据和 README。完整列表可在 [`src/`](src) 中查看。

## 文档

文档站提供四种语言：

- [简体中文](https://docs.hdj.me/zh-cn/)
- [繁體中文](https://docs.hdj.me/zh-tw/)
- [香港繁體](https://docs.hdj.me/zh-hk/)
- [English](https://docs.hdj.me/en/)

组件文档位于 `docs/<locale>/components/`。组件 README 与文档页面是两套独立内容，行为变更
可能需要同时更新两处。

## 仓库结构

```text
src/<component>/        可独立安装的组件包
tests/<Component>/      共用 Pest 测试套件
docs/<locale>/          四语言 VitePress 文档
types/                  使用 PHPStan 最高级别检查的 PHP 存根
bin/                    仓库维护、拆分和发布脚本
```

根包聚合所有组件。大部分组件通过 `ConfigProvider` 与 Hyperf 集成，少数组件是独立于框架的
库。

## 开发

在仓库根目录安装依赖：

```bash
composer install
```

运行标准本地检查：

```bash
composer test          # 代码风格、Pest 测试和类型覆盖率
composer analyse       # PHPStan 静态分析
composer analyse:types # 对 types/ 运行 PHPStan 最高级别分析
```

开发单个组件时，优先运行目标检查：

```bash
vendor/bin/pest --group cache
vendor/bin/pest tests/CoPhpunit
composer analyse src/cache
composer cs-fix -- src/cache
```

Pest 分组定义在 [`tests/Pest.php`](tests/Pest.php)。并非每个组件都有分组，必要时应按测试目录
或文件运行。`composer test` 不包含 PHPStan，提交代码变更前需要单独运行 `composer analyse`。

文档变更使用：

```bash
npm install
npm run docs:check
```

简体中文是翻译源。保持 `en`、`zh-cn`、`zh-hk` 和 `zh-tw` 的页面集合与标题结构同步，并在
提交前人工检查生成的翻译。`npm run docs:translate` 会修改翻译文件并依赖翻译服务配置。
仅在明确需要重新生成翻译时执行。

## 贡献

修改前请阅读 [CONTRIBUTE.md](CONTRIBUTE.md) 和 [AGENTS.md](AGENTS.md)。

- 以组件源码和测试为文档行为的权威依据。
- 公共行为变更时更新测试。
- 组件 README 或文档代码片段受影响时同步更新。
- 严格控制修改范围，不无故编辑生成文件或无关文件。
- 优先运行目标检查，再运行相关的仓库级检查。
- 使用 Conventional Commits，适用时添加组件 scope。

## 发布模型

本 monorepo 是源码权威。维护流程会把 `src/<component>` 拆分到独立的
`friendsofhyperf/<component>` 仓库，并为 monorepo 与组件仓库统一发布版本标签。

拆分和发布脚本会强制推送或为远端仓库打标签，仅供维护者使用，日常开发中不应执行。

## 社区

- [文档](https://docs.hdj.me/)
- [GitHub Issues](https://github.com/friendsofhyperf/components/issues)
- [CNB 镜像](https://cnb.cool/friendsofhyperf/components)
- [贡献者](https://github.com/friendsofhyperf/components/graphs/contributors)

## 许可证

FriendsOfHyperf Components 使用 [MIT License](LICENSE) 开源。
