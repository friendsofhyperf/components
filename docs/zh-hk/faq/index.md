# 常見問題

## 應該在哪裏報告問題？

請在 [components monorepo](https://github.com/friendsofhyperf/components/issues) 提交 Issue，
並提供組件名稱、已安裝版本、最小復現示例和完整異常堆棧。

## 應該安裝聚合包還是單個組件？

明確需要完整組件集時安裝 `friendsofhyperf/components`。大多數應用更適合按需安裝獨立的
`friendsofhyperf/*` 包，以減少依賴並只啓用需要的服務提供者。

## 組件會自動註冊嗎？

大多數組件提供 Hyperf `ConfigProvider`，並通過組件發現機制自動註冊。需要配置文件或數據庫
遷移的組件會在對應頁面説明發布命令。

## 支持哪些版本？

當前分支要求 PHP 8.2 或更高版本，並面向 Hyperf 3.2。準確的必需依賴和建議依賴請查看所選
組件的 `composer.json`。

## 為什麼示例還需要其他包？

部分功能會集成 AMQP、Kafka、異步隊列、Elasticsearch 或外部服務等可選依賴。Composer 的
`suggest` 段和組件文檔會標明這些依賴。

## 應該向哪裏提交 Pull Request？

所有 Pull Request 都應提交到
[components monorepo](https://github.com/friendsofhyperf/components)。獨立組件倉庫由自動拆分
生成，不接收貢獻。

更多信息請閲讀[關於 FriendsOfHyperf](about.md)和[如何使用組件](how-to-use.md)。
