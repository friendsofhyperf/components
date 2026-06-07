# Command Benchmark

A benchmarking component for Hyperf commands, forked from
[christophrumpel/artisan-benchmark](https://github.com/christophrumpel/artisan-benchmark).

## Installation

```shell
composer require friendsofhyperf/command-benchmark
```

The package declares Hyperf Collection, Command, and Event `~3.2.0` as dependencies.
Hyperf package discovery loads its `ConfigProvider`, which registers the component's AOP
aspect.

## Usage

The aspect adds the flag-only `--enable-benchmark` option to commands that extend
`Hyperf\Command\Command`. Add the option when invoking a command:

```shell
php bin/hyperf.php your:command --enable-benchmark
```

After the command's `execute()` method returns normally, the component prints a colored
summary surrounded by blank lines:

```text
⚡ TIME: 2.5s  MEM: 15.23MB  SQL: 42
```

Commands invoked without `--enable-benchmark` do not print the summary.

## Metrics

- **TIME**: elapsed time from immediately after the command constructor returns until its
  `execute()` method returns. Durations below one second are shown in milliseconds,
  durations below 60 seconds in seconds, and longer durations in minutes and seconds.
- **MEM**: the reported process-memory difference in MB, rounded to two decimal places.
  The implementation subtracts the value captured with `memory_get_usage(true)` from the
  final `memory_get_usage()` value. It is not peak memory usage and can be negative.
- **SQL**: the number of `Hyperf\Database\Events\QueryExecuted` events observed after the
  command constructor returns and before the summary is rendered. The listener observes
  every dispatched event of this type in the process, not only queries attributable to the
  command. The value remains `0` when no such events are dispatched.

Metric collection starts when each command is constructed, even when the command is later
invoked without `--enable-benchmark`. Therefore, the reported interval can include work
performed between command construction and command execution; it is not limited to the
body of `execute()`.

## Configuration

No configuration file is published. `FriendsOfHyperf\CommandBenchmark\ConfigProvider`
registers `FriendsOfHyperf\CommandBenchmark\Aspect\CommandAspect` automatically. If
Hyperf package discovery is disabled, ensure that this config provider is loaded.

There are no configuration keys or annotations. The operational interface is the
`--enable-benchmark` command option.

## Notes

- The repository currently has no dedicated tests for this component.
- Benchmark collection and SQL event listeners add overhead, so use the option primarily
  for development and diagnostics. Collection and listener registration happen when each
  command is constructed, regardless of whether the option is later used.
- Treat `--enable-benchmark` as a reserved option name; defining an incompatible option
  with the same name on a command causes option registration to fail.
- `hyperf/database` is not a declared dependency of this package; the SQL metric is only
  meaningful when the application dispatches its `QueryExecuted` events.
- The SQL metric counts dispatched `QueryExecuted` events; it does not inspect database
  connections directly.

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)
