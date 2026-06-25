# 組件

每個組件都可以使用 `composer require friendsofhyperf/<component>` 獨立安裝。只有明確需要
完整組件集時，才安裝 `friendsofhyperf/components`。

## 開發與診斷

- [Telescope](telescope.md)：檢查請求、異常、查詢、Redis 命令等運行信息。
- [Tinker](tinker.md)：在 Hyperf 應用中運行交互式 REPL。
- [Web Tinker](web-tinker.md)：通過瀏覽器使用 Tinker。
- [IDE Helper](ide-helper.md)：為應用類生成 IDE 元數據。
- [Pretty Console](pretty-console.md)：格式化命令行輸出。
- [Command Benchmark](command-benchmark.md)：測量命令執行過程。

## 數據庫與模型

- [Model Factory](model-factory.md)：為測試和種子數據創建模型工廠。
- [Model Observer](model-observer.md)：註冊模型觀察者。
- [Model Scope](model-scope.md)：定義可複用的模型查詢作用域。
- [Model Hashids](model-hashids.md)：對外提供編碼後的模型標識。
- [Model Morph Addon](model-morph-addon.md)：擴展多態模型關聯。
- [Compoships](compoships.md)：使用多列定義模型關聯。
- [Fast Paginate](fast-paginate.md)：優化大數據集分頁。
- [MySQL Grammar Addon](mysql-grammar-addon.md)：擴展 MySQL 查詢語法。
- [Trigger](trigger.md)：消費 MySQL binlog 事件。

## 緩存與協調

- [Cache](cache.md)：使用表達力更強的緩存 API。
- [Lock](lock.md)：通過分佈式鎖協調任務。
- [Redis Subscriber](redis-subscriber.md)：消費 Redis Pub/Sub 消息。

## HTTP 與外部服務

- [HTTP Client](http-client.md)：使用便捷的 HTTP 客户端。
- [OAuth2 Server](oauth2-server.md)：構建 OAuth 2.0 授權服務器。
- [OpenAI Client](openai-client.md)：集成 OpenAI PHP 客户端。
- [Elasticsearch](elasticsearch.md)：配置 Elasticsearch 客户端。
- [reCAPTCHA](recaptcha.md)：驗證 Google reCAPTCHA 響應。

## 消息與通知

- [AMQP Job](amqp-job.md)：將 AMQP 消息作為任務派發。
- [Mail](mail.md)：使用 Symfony Mailer 發送郵件。
- [Notification](notification.md)：通過多個渠道發送通知。
- [Notification Mail](notification-mail.md)：通過郵件發送通知。
- [Notification EasySms](notification-easysms.md)：通過短信發送通知。
- [TCP Sender](tcp-sender.md)：向 TCP 服務發送消息。

## 配置與基礎設施

- [Confd](confd.md)：使用 confd 管理配置。
- [Config Consul](config-consul.md)：從 Consul 加載配置。
- [IPC Broadcaster](ipc-broadcaster.md)：在 Worker 進程之間廣播消息。
- [Telescope Elasticsearch](telescope-elasticsearch.md)：將 Telescope 記錄存儲到 Elasticsearch。

## 安全與驗證

- [Encryption](encryption.md)：加密和解密應用數據。
- [Purifier](purifier.md)：清理 HTML 輸入。
- [Validated DTO](validated-dto.md)：驗證並填充數據傳輸對象。
- [Command Validation](command-validation.md)：驗證控制枱命令輸入。
- [gRPC Validation](grpc-validation.md)：驗證 gRPC 請求。
- [Rate Limit](rate-limit.md)：應用可配置的限流算法。

## 框架擴展

- [Facade](facade.md)：定義 Laravel 風格的 Facade。
- [Macros](macros.md)：為框架類添加宏。
- [Helpers](helpers.md)：使用額外的輔助函數。
- [Support](support.md)：使用共享工具、流式派發和退避策略。
- [Exception Event](exception-event.md)：為異常派發事件。

## 命令與運行時

- [Command Signals](command-signals.md)：在命令中處理進程信號。
- [Console Spinner](console-spinner.md)：顯示進度 Spinner。
- [Co-PHPUnit](co-phpunit.md)：在協程中運行 PHPUnit 測試。
