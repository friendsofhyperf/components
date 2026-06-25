# Command Benchmark

Hyperf 命令的基準測試元件，Fork 自
[christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark)。

## 安裝

```shell
composer require friendsofhyperf/command-benchmark
```

該包宣告依賴 Hyperf Collection、Command 和 Event `~3.2.0`。Hyperf 包發現機制會載入其
`ConfigProvider`，並註冊元件的 AOP 切面。

## 使用

該切面會為繼承 `Hyperf\Command\Command` 的命令新增無需值的
`--enable-benchmark` 選項。呼叫命令時新增該選項即可：

```shell
php bin/hyperf.php your:command --enable-benchmark
```

命令的 `execute()` 方法正常返回後，元件會輸出前後帶空行的彩色摘要：

```text
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

未傳入 `--enable-benchmark` 時不會輸出摘要。

## 指標

- **TIME**：從命令建構函式返回後到其 `execute()` 方法返回的耗時。小於一秒時顯示為毫秒，
  小於 60 秒時顯示為秒，更長時間顯示為分鐘和秒。
- **MEM**：程序記憶體差值的報告值，以 MB 為單位並保留兩位小數。實現會用最終的
  `memory_get_usage()` 值減去透過 `memory_get_usage(true)` 記錄的起始值；該值不是峰值記憶體，
  也可能為負數。
- **SQL**：從命令建構函式返回後到輸出摘要前觀察到的
  `Hyperf\Database\Events\QueryExecuted` 事件數量。監聽器會觀察程序內派發的所有此類事件，
  不僅限於可歸因於當前命令的查詢；未派發此類事件時為 `0`。

每個命令在構造時都會開始收集指標，即使之後呼叫命令時沒有傳入
`--enable-benchmark`。因此，報告的區間可能包含命令構造完成至執行之間的工作，並不僅限於
`execute()` 方法體。

## 配置

元件不會發布配置檔案。`FriendsOfHyperf\CommandBenchmark\ConfigProvider` 會自動註冊
`FriendsOfHyperf\CommandBenchmark\Aspect\CommandAspect`。如果停用了 Hyperf 包發現機制，
請確保載入該配置提供器。

元件沒有配置項或註解；其操作入口是 `--enable-benchmark` 命令選項。

## 注意事項

- 倉庫目前沒有該元件的專用測試。
- 指標收集和 SQL 事件監聽器會產生開銷，建議主要用於開發和診斷。無論之後是否使用該選項，
  每個命令構造時都會收集指標並註冊監聽器。
- 應將 `--enable-benchmark` 視為保留選項名；命令自行定義同名但不相容的選項會導致選項註冊失敗。
- `hyperf/database` 不是該包宣告的依賴；僅當應用派發其 `QueryExecuted` 事件時，SQL 指標
  才有意義。
- SQL 指標統計已派發的 `QueryExecuted` 事件，不會直接檢查資料庫連線。

## 聯絡方式

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
