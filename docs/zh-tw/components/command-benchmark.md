# Command Benchmark

Hyperf 命令的基準測試組件，Fork 自 [christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark)。

## 安裝

```shell
composer require friendsofhyperf/command-benchmark
```

## 介紹

Command Benchmark 組件為 Hyperf 命令提供了效能基準測試功能。它可以自動收集並顯示命令執行的效能指標，包括：

- **執行時間**：命令執行所需的時間
- **記憶體使用**：命令執行期間使用的記憶體
- **資料庫查詢次數**：命令執行期間執行的 SQL 查詢數量

## 使用

該組件透過 AOP（面向切面編程）自動為所有 Hyperf 命令新增 `--enable-benchmark` 選項。

### 啟用基準測試

在執行任何命令時，只需新增 `--enable-benchmark` 選項即可啟用基準測試：

```shell
php bin/hyperf.php your:command --enable-benchmark
```

### 輸出範例

命令執行完成後，會在輸出末尾顯示基準測試結果：

```
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

各指標說明：
- **TIME**：執行時間（毫秒、秒或分鐘）
- **MEM**：記憶體使用量（MB）
- **SQL**：執行的 SQL 查詢次數

## 工作原理

該組件使用 Hyperf 的 AOP 功能攔截命令的建構和執行：

1. **建構階段**：
   - 記錄開始時間和記憶體使用
   - 註冊資料庫查詢事件監聽器
   - 為命令新增 `--enable-benchmark` 選項

2. **執行階段**：
   - 如果啟用了 `--enable-benchmark` 選項
   - 計算執行時間、記憶體使用和查詢次數
   - 格式化並顯示基準測試結果

3. **結果顯示**：
   - 使用彩色輸出顯示各項指標
   - 自動格式化時間（毫秒、秒、分鐘）
   - 在命令輸出末尾顯示結果

## 設定

該組件無需額外設定，安裝後即可使用。組件會自動註冊到 Hyperf 容器中。

## 技術細節

### AOP 切面

組件透過 `CommandAspect` 切面類別攔截 `Hyperf\Command\Command` 類別的以下方法：
- `__construct`：初始化效能指標收集
- `execute`：執行完成後顯示基準測試結果

### 效能指標

- **執行時間**：使用 `microtime(true)` 測量
- **記憶體使用**：使用 `memory_get_usage()` 測量
- **查詢次數**：透過監聽 `QueryExecuted` 事件統計

### 時間格式化

時間會根據執行時長自動選擇合適的單位：
- 小於 1 秒：顯示為毫秒（如 `250ms`）
- 1 秒到 60 秒：顯示為秒（如 `2.5s`）
- 大於 60 秒：顯示為分鐘和秒（如 `2m 30s`）

## 範例

### 測試資料匯入命令

```shell
php bin/hyperf.php import:users --enable-benchmark
```

輸出：
```
Importing users...
100 users imported successfully.

⚡ TIME: 5.23s  MEM: 28.45MB  SQL: 150
```

### 測試快取清除命令

```shell
php bin/hyperf.php cache:clear --enable-benchmark
```

輸出：
```
Cache cleared successfully.

⚡ TIME: 120ms  MEM: 2.15MB  SQL: 0
```

## 注意事項

1. 基準測試會帶來輕微的效能開銷，建議只在開發和除錯時使用
2. SQL 查詢統計包括所有透過 Hyperf 資料庫組件執行的查詢
3. 記憶體使用量是相對值，表示命令執行期間增加的記憶體使用量

## 聯絡方式

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
