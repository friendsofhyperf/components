# 關於 FriendsOfHyperf

## FriendsOfHyperf 是什麼？

FriendsOfHyperf 是維護 Hyperf 可複用元件的社群專案。元件集涵蓋開發工具、資料庫輔助功能、
訊息通訊、資料驗證、監控以及外部服務整合。

## 倉庫如何組織？

`components` 倉庫是唯一事實來源。`src/` 下的每個目錄都是可獨立安裝的 Composer 包，
而 `friendsofhyperf/components` 會安裝完整元件集。

## 元件如何釋出？

釋出流程會將 monorepo 拆分為只讀的獨立元件倉庫。Issue 和 Pull Request 都應提交到 monorepo。

## 文件如何維護？

文件站點使用 VitePress，併為四種語言維護路徑一致的頁面。提交文件變更前，請在倉庫根目錄執行
`npm run docs:check`。
