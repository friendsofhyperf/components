# Command Benchmark

The benchmark component for Hyperf Command, Forked from [christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark).

## Installation

```shell
composer require friendsofhyperf/command-benchmark
```

## Introduction

Command Benchmark component provides performance benchmarking functionality for Hyperf commands. It automatically collects and displays performance metrics for command execution, including:

- **Execution Time**: The time taken for the command to run
- **Memory Usage**: Memory consumed during command execution
- **Database Queries**: Number of SQL queries executed during command execution

## Usage

The component automatically adds the `--enable-benchmark` option to all Hyperf commands via AOP (Aspect-Oriented Programming).

### Enable Benchmarking

Simply add the `--enable-benchmark` option when running any command:

```shell
php bin/hyperf.php your:command --enable-benchmark
```

### Output Example

After the command completes, benchmark results will be displayed at the end of the output:

```
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

Metrics explanation:
- **TIME**: Execution time (milliseconds, seconds, or minutes)
- **MEM**: Memory usage (MB)
- **SQL**: Number of SQL queries executed

## How It Works

The component uses Hyperf's AOP functionality to intercept command construction and execution:

1. **Construction Phase**:
   - Records start time and memory usage
   - Registers database query event listener
   - Adds `--enable-benchmark` option to the command

2. **Execution Phase**:
   - If `--enable-benchmark` option is enabled
   - Calculates execution time, memory usage, and query count
   - Formats and displays benchmark results

3. **Result Display**:
   - Uses colored output to display metrics
   - Automatically formats time (milliseconds, seconds, minutes)
   - Displays results at the end of command output

## Configuration

This component requires no additional configuration and works out of the box after installation. The component is automatically registered with the Hyperf container.

## Technical Details

### AOP Aspect

The component intercepts the following methods of the `Hyperf\Command\Command` class via the `CommandAspect` aspect:
- `__construct`: Initializes performance metric collection
- `execute`: Displays benchmark results after execution completes

### Performance Metrics

- **Execution Time**: Measured using `microtime(true)`
- **Memory Usage**: Measured using `memory_get_usage()`
- **Query Count**: Counted by listening to `QueryExecuted` events

### Time Formatting

Time is automatically formatted with appropriate units based on duration:
- Less than 1 second: Displayed in milliseconds (e.g., `250ms`)
- 1 to 60 seconds: Displayed in seconds (e.g., `2.5s`)
- More than 60 seconds: Displayed in minutes and seconds (e.g., `2m 30s`)

## Examples

### Testing Data Import Command

```shell
php bin/hyperf.php import:users --enable-benchmark
```

Output:
```
Importing users...
100 users imported successfully.

⚡ TIME: 5.23s  MEM: 28.45MB  SQL: 150
```

### Testing Cache Clear Command

```shell
php bin/hyperf.php cache:clear --enable-benchmark
```

Output:
```
Cache cleared successfully.

⚡ TIME: 120ms  MEM: 2.15MB  SQL: 0
```

## Notes

1. Benchmarking adds slight performance overhead; recommended for development and debugging only
2. SQL query statistics include all queries executed through Hyperf's database component
3. Memory usage is a relative value, representing the increase in memory usage during command execution

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
