# 關於 FriendsOfHyperf

## FriendsOfHyperf 是什麼？

FriendsOfHyperf 是維護 Hyperf 可複用組件的社區項目。組件集涵蓋開發工具、數據庫輔助功能、
消息通信、數據驗證、監控以及外部服務集成。

## 倉庫如何組織？

`components` 倉庫是唯一事實來源。`src/` 下的每個目錄都是可獨立安裝的 Composer 包，
而 `friendsofhyperf/components` 會安裝完整組件集。

## 組件如何發佈？

發佈流程會將 monorepo 拆分為只讀的獨立組件倉庫。Issue 和 Pull Request 都應提交到 monorepo。

## 文檔如何維護？

文檔站點使用 VitePress，併為四種語言維護路徑一致的頁面。提交文檔變更前，請在倉庫根目錄運行
`npm run docs:check`。
