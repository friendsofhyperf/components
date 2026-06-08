# 元件

每個元件都可以使用 `composer require friendsofhyperf/<component>` 獨立安裝。只有明確需要
完整元件集時，才安裝 `friendsofhyperf/components`。

## 開發與診斷

- [Telescope](telescope.md)：檢查請求、例外、查詢、Redis 指令等執行資訊。
- [Tinker](tinker.md)：在 Hyperf 應用程式中執行互動式 REPL。
- [Web Tinker](web-tinker.md)：透過瀏覽器使用 Tinker。
- [IDE Helper](ide-helper.md)：為應用程式類別產生 IDE 中繼資料。
- [Pretty Console](pretty-console.md)：格式化命令列輸出。
- [Command Benchmark](command-benchmark.md)：測量指令執行過程。

## 資料庫與模型

- [Model Factory](model-factory.md)：為測試和種子資料建立模型工廠。
- [Model Observer](model-observer.md)：註冊模型觀察者。
- [Model Scope](model-scope.md)：定義可重用的模型查詢範圍。
- [Model Hashids](model-hashids.md)：對外提供編碼後的模型識別碼。
- [Model Morph Addon](model-morph-addon.md)：擴充多型模型關聯。
- [Compoships](compoships.md)：使用多欄定義模型關聯。
- [Fast Paginate](fast-paginate.md)：最佳化大型資料集分頁。
- [MySQL Grammar Addon](mysql-grammar-addon.md)：擴充 MySQL 查詢語法。
- [Trigger](trigger.md)：消費 MySQL binlog 事件。

## 快取與協調

- [Cache](cache.md)：使用表達力更強的快取 API。
- [Lock](lock.md)：透過分散式鎖協調工作。
- [Redis Subscriber](redis-subscriber.md)：消費 Redis Pub/Sub 訊息。

## HTTP 與外部服務

- [HTTP Client](http-client.md)：使用便利的 HTTP 用戶端。
- [OAuth2 Server](oauth2-server.md)：建立 OAuth 2.0 授權伺服器。
- [OpenAI Client](openai-client.md)：整合 OpenAI PHP 用戶端。
- [Elasticsearch](elasticsearch.md)：設定 Elasticsearch 用戶端。
- [reCAPTCHA](recaptcha.md)：驗證 Google reCAPTCHA 回應。

## 訊息與通知

- [AMQP Job](amqp-job.md)：將 AMQP 訊息作為工作派送。
- [Mail](mail.md)：使用 Symfony Mailer 傳送郵件。
- [Notification](notification.md)：透過多個管道傳送通知。
- [Notification Mail](notification-mail.md)：透過郵件傳送通知。
- [Notification EasySms](notification-easysms.md)：透過簡訊傳送通知。
- [TCP Sender](tcp-sender.md)：向 TCP 服務傳送訊息。

## 設定與基礎設施

- [Confd](confd.md)：使用 confd 管理設定。
- [Config Consul](config-consul.md)：從 Consul 載入設定。
- [IPC Broadcaster](ipc-broadcaster.md)：在 Worker 行程之間廣播訊息。
- [Telescope Elasticsearch](telescope-elasticsearch.md)：將 Telescope 記錄儲存到 Elasticsearch。

## 安全與驗證

- [Encryption](encryption.md)：加密和解密應用程式資料。
- [Purifier](purifier.md)：清理 HTML 輸入。
- [Validated DTO](validated-dto.md)：驗證並填入資料傳輸物件。
- [Command Validation](command-validation.md)：驗證主控台指令輸入。
- [gRPC Validation](grpc-validation.md)：驗證 gRPC 請求。
- [Rate Limit](rate-limit.md)：套用可設定的速率限制演算法。

## 框架擴充

- [Facade](facade.md)：定義 Laravel 風格的 Facade。
- [Macros](macros.md)：為框架類別加入巨集。
- [Helpers](helpers.md)：使用額外的輔助函式。
- [Support](support.md)：使用共用工具、流暢派送和退避策略。
- [Exception Event](exception-event.md)：為例外派送事件。

## 指令與執行環境

- [Command Signals](command-signals.md)：在指令中處理行程訊號。
- [Console Spinner](console-spinner.md)：顯示進度 Spinner。
- [Co-PHPUnit](co-phpunit.md)：在協程中執行 PHPUnit 測試。
