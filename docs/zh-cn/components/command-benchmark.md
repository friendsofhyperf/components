# Command Benchmark

Hyperf 命令的基准测试组件，Fork 自 [christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark)。

## 安装

```shell
composer require friendsofhyperf/command-benchmark
```

## 介绍

Command Benchmark 组件为 Hyperf 命令提供了性能基准测试功能。它可以自动收集并显示命令执行的性能指标，包括：

- **执行时间**：命令运行所需的时间
- **内存使用**：命令执行期间使用的内存
- **数据库查询次数**：命令执行期间执行的 SQL 查询数量

## 使用

该组件通过 AOP（面向切面编程）自动为所有 Hyperf 命令添加 `--enable-benchmark` 选项。

### 启用基准测试

在运行任何命令时，只需添加 `--enable-benchmark` 选项即可启用基准测试：

```shell
php bin/hyperf.php your:command --enable-benchmark
```

### 输出示例

命令执行完成后，会在输出末尾显示基准测试结果：

```
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

各指标说明：
- **TIME**：执行时间（毫秒、秒或分钟）
- **MEM**：内存使用量（MB）
- **SQL**：执行的 SQL 查询次数

## 工作原理

该组件使用 Hyperf 的 AOP 功能拦截命令的构造和执行：

1. **构造阶段**：
   - 记录开始时间和内存使用
   - 注册数据库查询事件监听器
   - 为命令添加 `--enable-benchmark` 选项

2. **执行阶段**：
   - 如果启用了 `--enable-benchmark` 选项
   - 计算执行时间、内存使用和查询次数
   - 格式化并显示基准测试结果

3. **结果显示**：
   - 使用彩色输出显示各项指标
   - 自动格式化时间（毫秒、秒、分钟）
   - 在命令输出末尾显示结果

## 配置

该组件无需额外配置，安装后即可使用。组件会自动注册到 Hyperf 容器中。

## 技术细节

### AOP 切面

组件通过 `CommandAspect` 切面类拦截 `Hyperf\Command\Command` 类的以下方法：
- `__construct`：初始化性能指标收集
- `execute`：执行完成后显示基准测试结果

### 性能指标

- **执行时间**：使用 `microtime(true)` 测量
- **内存使用**：使用 `memory_get_usage()` 测量
- **查询次数**：通过监听 `QueryExecuted` 事件统计

### 时间格式化

时间会根据执行时长自动选择合适的单位：
- 小于 1 秒：显示为毫秒（如 `250ms`）
- 1 秒到 60 秒：显示为秒（如 `2.5s`）
- 大于 60 秒：显示为分钟和秒（如 `2m 30s`）

## 示例

### 测试数据导入命令

```shell
php bin/hyperf.php import:users --enable-benchmark
```

输出：
```
Importing users...
100 users imported successfully.

⚡ TIME: 5.23s  MEM: 28.45MB  SQL: 150
```

### 测试缓存清理命令

```shell
php bin/hyperf.php cache:clear --enable-benchmark
```

输出：
```
Cache cleared successfully.

⚡ TIME: 120ms  MEM: 2.15MB  SQL: 0
```

## 注意事项

1. 基准测试会带来轻微的性能开销，建议只在开发和调试时使用
2. SQL 查询统计包括所有通过 Hyperf 数据库组件执行的查询
3. 内存使用量是相对值，表示命令执行期间增加的内存使用量

## 联系方式

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
