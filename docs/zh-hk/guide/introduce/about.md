# 關於 Hyperf Fans

FriendsOfHyperf 是由社區維護的 Hyperf 擴展組件集合，提供面向生產環境的集成能力，
並帶來部分受 Laravel 啓發的開發體驗。

## 倉庫模式

源代碼統一維護在
[friendsofhyperf/components](https://github.com/friendsofhyperf/components) monorepo 中。
`src/` 下的每個目錄也會作為可獨立安裝的 Composer 包發佈。請向 monorepo 提交變更，
不要向自動拆分生成的獨立倉庫提交。

## 兼容性

當前分支面向 PHP 8.2 或更高版本以及 Hyperf 3.2。各組件頁面會説明可選集成和額外依賴。

## 文檔語言

文檔同時維護英文、簡體中文、香港繁體中文和台灣繁體中文。每次文檔變更都必須保持四種語言
的頁面路徑與標題結構一致。

## 參與貢獻

請在 monorepo 中提交 Issue 和 Pull Request。行為變更應在同一個 Pull Request 中更新組件測試
以及全部四種語言的文檔。
