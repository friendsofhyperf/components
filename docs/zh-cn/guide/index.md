# 入门

FriendsOfHyperf Components 是一组面向 Hyperf 3.2 的独立组件，采用 monorepo 统一维护。
你可以安装完整组件集，也可以只安装应用需要的单个组件。

## 环境要求

- PHP 8.2 或更高版本
- Hyperf 3.2
- 根据 Hyperf 应用选择 Swoole 或 Swow

## 安装完整组件集

```shell
composer require friendsofhyperf/components
```

聚合包会替代所有独立组件包，并注册其中可用的 `ConfigProvider`。

## 安装单个组件

```shell
composer require friendsofhyperf/cache
```

将 `cache` 替换为[组件目录](../components/index.md)中的包名。大多数组件会通过 Hyperf
组件发现机制自动注册；如果组件提供配置文件，请按对应组件页面的说明发布配置。

## 选择组件

- 开发与诊断：Telescope、Tinker、Web Tinker、IDE Helper
- 数据库与模型：Model Factory、Model Observer、Compoships、Fast Paginate
- 基础设施：Cache、Lock、Config Consul、Redis Subscriber
- 安全与验证：Encryption、Purifier、reCAPTCHA、Validated DTO
- 通信与消息：Mail、Notification、EasySms、AMQP Job、TCP Sender

完整分类请查看[组件目录](../components/index.md)。

## 后续步骤

1. 打开组件页面，确认依赖和环境要求。
2. 安装对应的独立组件包。
3. 在组件要求时发布配置。
4. 为应用中的集成行为添加有针对性的测试。
