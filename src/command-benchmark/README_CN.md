# Command Benchmark

[English](README.md)

Hyperf 命令的基准测试组件，Fork 自
[christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark)。

## 安装

```shell
composer require friendsofhyperf/command-benchmark
```

该包声明依赖 Hyperf Collection、Command 和 Event `~3.2.0`。Hyperf 包发现机制会加载其
`ConfigProvider`，并注册组件的 AOP 切面。

## 使用

该切面会为继承 `Hyperf\Command\Command` 的命令添加无需值的
`--enable-benchmark` 选项。调用命令时添加该选项即可：

```shell
php bin/hyperf.php your:command --enable-benchmark
```

命令的 `execute()` 方法正常返回后，组件会输出前后带空行的彩色摘要：

```text
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

未传入 `--enable-benchmark` 时不会输出摘要。

## 指标

- **TIME**：从命令构造函数返回后到其 `execute()` 方法返回的耗时。小于一秒时显示为毫秒，
  小于 60 秒时显示为秒，更长时间显示为分钟和秒。
- **MEM**：进程内存差值的报告值，以 MB 为单位并保留两位小数。实现会用最终的
  `memory_get_usage()` 值减去通过 `memory_get_usage(true)` 记录的起始值；该值不是峰值内存，
  也可能为负数。
- **SQL**：从命令构造函数返回后到输出摘要前观察到的
  `Hyperf\Database\Events\QueryExecuted` 事件数量。监听器会观察进程内派发的所有此类事件，
  不仅限于可归因于当前命令的查询；未派发此类事件时为 `0`。

每个命令在构造时都会开始收集指标，即使之后调用命令时没有传入
`--enable-benchmark`。因此，报告的区间可能包含命令构造完成至执行之间的工作，并不仅限于
`execute()` 方法体。

## 配置

组件不会发布配置文件。`FriendsOfHyperf\CommandBenchmark\ConfigProvider` 会自动注册
`FriendsOfHyperf\CommandBenchmark\Aspect\CommandAspect`。如果禁用了 Hyperf 包发现机制，
请确保加载该配置提供器。

组件没有配置项或注解；其操作入口是 `--enable-benchmark` 命令选项。

## 注意事项

- 仓库目前没有该组件的专用测试。
- 指标收集和 SQL 事件监听器会产生开销，建议主要用于开发和诊断。无论之后是否使用该选项，
  每个命令构造时都会收集指标并注册监听器。
- 应将 `--enable-benchmark` 视为保留选项名；命令自行定义同名但不兼容的选项会导致选项注册失败。
- `hyperf/database` 不是该包声明的依赖；仅当应用派发其 `QueryExecuted` 事件时，SQL 指标
  才有意义。
- SQL 指标统计已派发的 `QueryExecuted` 事件，不会直接检查数据库连接。

## 联系方式

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
