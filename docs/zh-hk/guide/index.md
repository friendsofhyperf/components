# 入門

FriendsOfHyperf Components 是一組面向 Hyperf 3.2 的獨立組件，採用 monorepo 統一維護。
你可以安裝完整組件集，也可以只安裝應用需要的單個組件。

## 環境要求

- PHP 8.2 或更高版本
- Hyperf 3.2
- 根據 Hyperf 應用選擇 Swoole 或 Swow

## 安裝完整組件集

```shell
composer require friendsofhyperf/components
```

聚合包會替代所有獨立組件包，並註冊其中可用的 `ConfigProvider`。

## 安裝單個組件

```shell
composer require friendsofhyperf/cache
```

將 `cache` 替換為[組件目錄](../components/index.md)中的包名。大多數組件會通過 Hyperf
組件發現機制自動註冊；如果組件提供配置文件，請按對應組件頁面的説明發布配置。

## 選擇組件

- 開發與診斷：Telescope、Tinker、Web Tinker、IDE Helper
- 數據庫與模型：Model Factory、Model Observer、Compoships、Fast Paginate
- 基礎設施：Cache、Lock、Config Consul、Redis Subscriber
- 安全與驗證：Encryption、Purifier、reCAPTCHA、Validated DTO
- 通信與消息：Mail、Notification、EasySms、AMQP Job、TCP Sender

完整分類請查看[組件目錄](../components/index.md)。

## 後續步驟

1. 打開組件頁面，確認依賴和環境要求。
2. 安裝對應的獨立組件包。
3. 在組件要求時發佈配置。
4. 為應用中的集成行為添加有針對性的測試。
