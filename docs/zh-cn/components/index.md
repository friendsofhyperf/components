# 组件

每个组件都可以使用 `composer require friendsofhyperf/<component>` 独立安装。只有明确需要
完整组件集时，才安装 `friendsofhyperf/components`。

## 开发与诊断

- [Telescope](telescope.md)：检查请求、异常、查询、Redis 命令等运行信息。
- [Tinker](tinker.md)：在 Hyperf 应用中运行交互式 REPL。
- [Web Tinker](web-tinker.md)：通过浏览器使用 Tinker。
- [IDE Helper](ide-helper.md)：为应用类生成 IDE 元数据。
- [Pretty Console](pretty-console.md)：格式化命令行输出。
- [Command Benchmark](command-benchmark.md)：测量命令执行过程。

## 数据库与模型

- [Model Factory](model-factory.md)：为测试和种子数据创建模型工厂。
- [Model Observer](model-observer.md)：注册模型观察者。
- [Model Scope](model-scope.md)：定义可复用的模型查询作用域。
- [Model Hashids](model-hashids.md)：对外提供编码后的模型标识。
- [Model Morph Addon](model-morph-addon.md)：扩展多态模型关联。
- [Compoships](compoships.md)：使用多列定义模型关联。
- [Fast Paginate](fast-paginate.md)：优化大数据集分页。
- [MySQL Grammar Addon](mysql-grammar-addon.md)：扩展 MySQL 查询语法。
- [Trigger](trigger.md)：消费 MySQL binlog 事件。

## 缓存与协调

- [Cache](cache.md)：使用表达力更强的缓存 API。
- [Lock](lock.md)：通过分布式锁协调任务。
- [Redis Subscriber](redis-subscriber.md)：消费 Redis Pub/Sub 消息。

## HTTP 与外部服务

- [HTTP Client](http-client.md)：使用便捷的 HTTP 客户端。
- [OAuth2 Server](oauth2-server.md)：构建 OAuth 2.0 授权服务器。
- [OpenAI Client](openai-client.md)：集成 OpenAI PHP 客户端。
- [Elasticsearch](elasticsearch.md)：配置 Elasticsearch 客户端。
- [reCAPTCHA](recaptcha.md)：验证 Google reCAPTCHA 响应。

## 消息与通知

- [AMQP Job](amqp-job.md)：将 AMQP 消息作为任务派发。
- [Mail](mail.md)：使用 Symfony Mailer 发送邮件。
- [Notification](notification.md)：通过多个渠道发送通知。
- [Notification Mail](notification-mail.md)：通过邮件发送通知。
- [Notification EasySms](notification-easysms.md)：通过短信发送通知。
- [TCP Sender](tcp-sender.md)：向 TCP 服务发送消息。

## 配置与基础设施

- [Confd](confd.md)：使用 confd 管理配置。
- [Config Consul](config-consul.md)：从 Consul 加载配置。
- [IPC Broadcaster](ipc-broadcaster.md)：在 Worker 进程之间广播消息。
- [Telescope Elasticsearch](telescope-elasticsearch.md)：将 Telescope 记录存储到 Elasticsearch。

## 安全与验证

- [Encryption](encryption.md)：加密和解密应用数据。
- [Purifier](purifier.md)：清理 HTML 输入。
- [Validated DTO](validated-dto.md)：验证并填充数据传输对象。
- [Command Validation](command-validation.md)：验证控制台命令输入。
- [gRPC Validation](grpc-validation.md)：验证 gRPC 请求。
- [Rate Limit](rate-limit.md)：应用可配置的限流算法。

## 框架扩展

- [Facade](facade.md)：定义 Laravel 风格的 Facade。
- [Macros](macros.md)：为框架类添加宏。
- [Helpers](helpers.md)：使用额外的辅助函数。
- [Support](support.md)：使用共享工具、流式派发和退避策略。
- [Exception Event](exception-event.md)：为异常派发事件。

## 命令与运行时

- [Command Signals](command-signals.md)：在命令中处理进程信号。
- [Console Spinner](console-spinner.md)：显示进度 Spinner。
- [Co-PHPUnit](co-phpunit.md)：在协程中运行 PHPUnit 测试。
