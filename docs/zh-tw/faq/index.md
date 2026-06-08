# 常見問題

## 應該在哪裡回報問題？

請在 [components monorepo](https://github.com/friendsofhyperf/components/issues) 提交 Issue，
並提供元件名稱、已安裝版本、最小重現範例和完整例外堆疊。

## 應該安裝聚合套件還是單一元件？

明確需要完整元件集時安裝 `friendsofhyperf/components`。大多數應用程式更適合依需求安裝獨立的
`friendsofhyperf/*` 套件，以減少相依套件並只啟用需要的服務提供者。

## 元件會自動註冊嗎？

大多數元件提供 Hyperf `ConfigProvider`，並透過元件探索機制自動註冊。需要設定檔或資料庫
遷移的元件會在對應頁面說明發布指令。

## 支援哪些版本？

目前分支要求 PHP 8.2 或更新版本，並適用於 Hyperf 3.2。準確的必要相依套件和建議相依套件請查看
所選元件的 `composer.json`。

## 為什麼範例還需要其他套件？

部分功能會整合 AMQP、Kafka、非同步佇列、Elasticsearch 或外部服務等選用相依套件。Composer 的
`suggest` 區段和元件文件會標明這些相依套件。

## 應該向哪裡提交 Pull Request？

所有 Pull Request 都應提交到
[components monorepo](https://github.com/friendsofhyperf/components)。獨立元件儲存庫由自動拆分
產生，不接收貢獻。

更多資訊請閱讀[關於 FriendsOfHyperf](about.md)和[如何使用元件](how-to-use.md)。
