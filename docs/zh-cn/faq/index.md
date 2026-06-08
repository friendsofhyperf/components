# 常见问题

## 应该在哪里报告问题？

请在 [components monorepo](https://github.com/friendsofhyperf/components/issues) 提交 Issue，
并提供组件名称、已安装版本、最小复现示例和完整异常堆栈。

## 应该安装聚合包还是单个组件？

明确需要完整组件集时安装 `friendsofhyperf/components`。大多数应用更适合按需安装独立的
`friendsofhyperf/*` 包，以减少依赖并只启用需要的服务提供者。

## 组件会自动注册吗？

大多数组件提供 Hyperf `ConfigProvider`，并通过组件发现机制自动注册。需要配置文件或数据库
迁移的组件会在对应页面说明发布命令。

## 支持哪些版本？

当前分支要求 PHP 8.2 或更高版本，并面向 Hyperf 3.2。准确的必需依赖和建议依赖请查看所选
组件的 `composer.json`。

## 为什么示例还需要其他包？

部分功能会集成 AMQP、Kafka、异步队列、Elasticsearch 或外部服务等可选依赖。Composer 的
`suggest` 段和组件文档会标明这些依赖。

## 应该向哪里提交 Pull Request？

所有 Pull Request 都应提交到
[components monorepo](https://github.com/friendsofhyperf/components)。独立组件仓库由自动拆分
生成，不接收贡献。

更多信息请阅读[关于 FriendsOfHyperf](about.md)和[如何使用组件](how-to-use.md)。
