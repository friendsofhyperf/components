# 元件

每個元件都可以使用 `composer require friendsofhyperf/<component>` 獨立安裝。只有明確需要
完整元件集時，才安裝 `friendsofhyperf/components`。

## 開發與診斷

- [Telescope](telescope.md)：檢查請求、異常、查詢、Redis 命令等執行資訊。
- [Tinker](tinker.md)：在 Hyperf 應用中執行互動式 REPL。
- [Web Tinker](web-tinker.md)：透過瀏覽器使用 Tinker。
- [IDE Helper](ide-helper.md)：為應用類生成 IDE 元資料。
- [Pretty Console](pretty-console.md)：格式化命令列輸出。
- [Command Benchmark](command-benchmark.md)：測量命令執行過程。

## 資料庫與模型

- [Model Factory](model-factory.md)：為測試和種子資料建立模型工廠。
- [Model Observer](model-observer.md)：註冊模型觀察者。
- [Model Scope](model-scope.md)：定義可複用的模型查詢作用域。
- [Model Hashids](model-hashids.md)：對外提供編碼後的模型標識。
- [Model Morph Addon](model-morph-addon.md)：擴充套件多型模型關聯。
- [Compoships](compoships.md)：使用多列定義模型關聯。
- [Fast Paginate](fast-paginate.md)：最佳化大資料集分頁。
- [MySQL Grammar Addon](mysql-grammar-addon.md)：擴充套件 MySQL 查詢語法。
- [Trigger](trigger.md)：消費 MySQL binlog 事件。

## 快取與協調

- [Cache](cache.md)：使用表達力更強的快取 API。
- [Lock](lock.md)：透過分散式鎖協調任務。
- [Redis Subscriber](redis-subscriber.md)：消費 Redis Pub/Sub 訊息。

## HTTP 與外部服務

- [HTTP Client](http-client.md)：使用便捷的 HTTP 客戶端。
- [OAuth2 Server](oauth2-server.md)：構建 OAuth 2.0 授權伺服器。
- [OpenAI Client](openai-client.md)：整合 OpenAI PHP 客戶端。
- [Elasticsearch](elasticsearch.md)：配置 Elasticsearch 客戶端。
- [reCAPTCHA](recaptcha.md)：驗證 Google reCAPTCHA 響應。

## 訊息與通知

- [AMQP Job](amqp-job.md)：將 AMQP 訊息作為任務派發。
- [Mail](mail.md)：使用 Symfony Mailer 傳送郵件。
- [Notification](notification.md)：透過多個渠道傳送通知。
- [Notification Mail](notification-mail.md)：透過郵件傳送通知。
- [Notification EasySms](notification-easysms.md)：透過簡訊傳送通知。
- [TCP Sender](tcp-sender.md)：向 TCP 服務傳送訊息。

## 配置與基礎設施

- [Confd](confd.md)：使用 confd 管理配置。
- [Config Consul](config-consul.md)：從 Consul 載入配置。
- [IPC Broadcaster](ipc-broadcaster.md)：在 Worker 程序之間廣播訊息。
- [Telescope Elasticsearch](telescope-elasticsearch.md)：將 Telescope 記錄儲存到 Elasticsearch。

## 安全與驗證

- [Encryption](encryption.md)：加密和解密應用資料。
- [Purifier](purifier.md)：清理 HTML 輸入。
- [Validated DTO](validated-dto.md)：驗證並填充資料傳輸物件。
- [Command Validation](command-validation.md)：驗證控制檯命令輸入。
- [gRPC Validation](grpc-validation.md)：驗證 gRPC 請求。
- [Rate Limit](rate-limit.md)：應用可配置的限流演算法。

## 框架擴充套件

- [Facade](facade.md)：定義 Laravel 風格的 Facade。
- [Macros](macros.md)：為框架類新增宏。
- [Helpers](helpers.md)：使用額外的輔助函式。
- [Support](support.md)：使用共享工具、流式派發和退避策略。
- [Exception Event](exception-event.md)：為異常派發事件。

## 命令與執行時

- [Command Signals](command-signals.md)：在命令中處理程序訊號。
- [Console Spinner](console-spinner.md)：顯示進度 Spinner。
- [Co-PHPUnit](co-phpunit.md)：在協程中執行 PHPUnit 測試。
