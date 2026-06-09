# Exception Event

[English](README.md)

此组件会在 Hyperf 的异常处理器调度器处理异常后派发 `ExceptionDispatched` 事件，并提供辅助函数，
用于在不抛出异常的情况下手动报告异常。

## 安装

```shell
composer require friendsofhyperf/exception-event
```

组件的 `ConfigProvider` 会通过 Composer 包发现机制自动注册切面。切面和监听器依赖 Hyperf 的 AOP
及事件调度器支持，标准 Hyperf 应用已提供这些支持；在最小化安装中，请确保
`hyperf/di` 和 `hyperf/event` 可用。

## 自动派发

注册的切面会拦截 `Hyperf\ExceptionHandler\ExceptionHandlerDispatcher::dispatch()`。调度器正常返回后，
组件会派发 `ExceptionDispatched` 事件，其中包含已处理的异常，以及上下文中的当前请求和响应。

## 定义监听器

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class ExceptionEventListener implements ListenerInterface
{
    public function __construct(private StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            ExceptionDispatched::class,
        ];
    }

    /**
     * @param ExceptionDispatched $event
     */
    public function process(object $event): void
    {
        $exception = $event->throwable;

        $this->logger->error(sprintf(
            'Exception: %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
    }
}
```

## 事件属性

`FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched` 公开以下属性：

- `Throwable $throwable`：已处理或手动报告的异常。
- `?ServerRequestInterface $request`：当前请求；不在请求上下文中时为 `null`。
- `?ResponseInterface $response`：当前响应；不在响应上下文中时为 `null`。

## 手动报告

组件会自动加载三个命名空间辅助函数：

- `report(string|Throwable $exception = 'RuntimeException', ...$parameters)`：报告异常。
- `report_if($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)`：
  条件为真值时报告。
- `report_unless($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)`：
  条件为假值时报告。

```php
use DomainException;

use function FriendsOfHyperf\ExceptionEvent\report;
use function FriendsOfHyperf\ExceptionEvent\report_if;
use function FriendsOfHyperf\ExceptionEvent\report_unless;

report(new DomainException('The operation failed.'));
report('The operation failed.'); // 使用此消息报告 RuntimeException。
report(DomainException::class, 'The operation failed.');

report_if($shouldReport, 'The operation failed.');
report_unless($operationSucceeded, 'The operation failed.');
```

`report()` 会直接派发 `ExceptionDispatched`，不会抛出异常。当第一个参数是已存在的异常类名时，
其余参数会传给该异常的构造函数；其他字符串会成为 `RuntimeException` 的消息。

`report_if()` 在条件为真值时报告，`report_unless()` 在条件为假值时报告。两个函数都会返回原始条件。
