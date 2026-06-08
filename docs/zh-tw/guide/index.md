# 入門

FriendsOfHyperf Components 是一組面向 Hyperf 3.2 的獨立元件，採用 monorepo 統一維護。
你可以安裝完整元件集，也可以只安裝應用程式需要的單一元件。

## 環境需求

- PHP 8.2 或更新版本
- Hyperf 3.2
- 依照 Hyperf 應用程式選擇 Swoole 或 Swow

## 安裝完整元件集

```shell
composer require friendsofhyperf/components
```

聚合套件會取代所有獨立元件套件，並註冊其中可用的 `ConfigProvider`。

## 安裝單一元件

```shell
composer require friendsofhyperf/cache
```

將 `cache` 替換為[元件目錄](../components/index.md)中的套件名稱。大多數元件會透過 Hyperf
元件探索機制自動註冊；如果元件提供設定檔，請依照對應元件頁面的說明發布設定。

## 選擇元件

- 開發與診斷：Telescope、Tinker、Web Tinker、IDE Helper
- 資料庫與模型：Model Factory、Model Observer、Compoships、Fast Paginate
- 基礎設施：Cache、Lock、Config Consul、Redis Subscriber
- 安全與驗證：Encryption、Purifier、reCAPTCHA、Validated DTO
- 通訊與訊息：Mail、Notification、EasySms、AMQP Job、TCP Sender

完整分類請查看[元件目錄](../components/index.md)。

## 後續步驟

1. 開啟元件頁面，確認相依套件與環境需求。
2. 安裝對應的獨立元件套件。
3. 在元件要求時發布設定。
4. 為應用程式中的整合行為加入針對性測試。
