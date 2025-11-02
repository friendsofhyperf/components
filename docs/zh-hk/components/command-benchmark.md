# Command Benchmark

Hyperf 命令的基準測試組件，Fork 自 [christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark)。

## 安裝

```shell
composer require friendsofhyperf/command-benchmark
```

## 介紹

Command Benchmark 組件為 Hyperf 命令提供了性能基準測試功能。它可以自動收集並顯示命令執行的性能指標，包括：

- **執行時間**：命令運行所需的時間
- **內存使用**：命令執行期間使用的內存
- **數據庫查詢次數**：命令執行期間執行的 SQL 查詢數量

## 使用

該組件通過 AOP（面向切面編程）自動為所有 Hyperf 命令添加 `--enable-benchmark` 選項。

### 啓用基準測試

在運行任何命令時，只需添加 `--enable-benchmark` 選項即可啓用基準測試：

```shell
php bin/hyperf.php your:command --enable-benchmark
```

### 輸出示例

命令執行完成後，會在輸出末尾顯示基準測試結果：

```
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

各指標説明：
- **TIME**：執行時間（毫秒、秒或分鐘）
- **MEM**：內存使用量（MB）
- **SQL**：執行的 SQL 查詢次數

## 工作原理

該組件使用 Hyperf 的 AOP 功能攔截命令的構造和執行：

1. **構造階段**：
   - 記錄開始時間和內存使用
   - 註冊數據庫查詢事件監聽器
   - 為命令添加 `--enable-benchmark` 選項

2. **執行階段**：
   - 如果啓用了 `--enable-benchmark` 選項
   - 計算執行時間、內存使用和查詢次數
   - 格式化並顯示基準測試結果

3. **結果顯示**：
   - 使用彩色輸出顯示各項指標
   - 自動格式化時間（毫秒、秒、分鐘）
   - 在命令輸出末尾顯示結果

## 配置

該組件無需額外配置，安裝後即可使用。組件會自動註冊到 Hyperf 容器中。

## 技術細節

### AOP 切面

組件通過 `CommandAspect` 切面類攔截 `Hyperf\Command\Command` 類的以下方法：
- `__construct`：初始化性能指標收集
- `execute`：執行完成後顯示基準測試結果

### 性能指標

- **執行時間**：使用 `microtime(true)` 測量
- **內存使用**：使用 `memory_get_usage()` 測量
- **查詢次數**：通過監聽 `QueryExecuted` 事件統計

### 時間格式化

時間會根據執行時長自動選擇合適的單位：
- 小於 1 秒：顯示為毫秒（如 `250ms`）
- 1 秒到 60 秒：顯示為秒（如 `2.5s`）
- 大於 60 秒：顯示為分鐘和秒（如 `2m 30s`）

## 示例

### 測試數據導入命令

```shell
php bin/hyperf.php import:users --enable-benchmark
```

輸出：
```
Importing users...
100 users imported successfully.

⚡ TIME: 5.23s  MEM: 28.45MB  SQL: 150
```

### 測試緩存清理命令

```shell
php bin/hyperf.php cache:clear --enable-benchmark
```

輸出：
```
Cache cleared successfully.

⚡ TIME: 120ms  MEM: 2.15MB  SQL: 0
```

## 注意事項

1. 基準測試會帶來輕微的性能開銷，建議只在開發和調試時使用
2. SQL 查詢統計包括所有通過 Hyperf 數據庫組件執行的查詢
3. 內存使用量是相對值，表示命令執行期間增加的內存使用量

## 聯繫方式

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
