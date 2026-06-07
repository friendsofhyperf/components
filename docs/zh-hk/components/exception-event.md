# Exception Event

此元件會在 Hyperf 的異常處理器調度器處理異常後派發 `ExceptionDispatched` 事件，並提供輔助函數，
用於在不拋出異常的情況下手動報告異常。

## 安裝

```shell
composer require friendsofhyperf/exception-event
```

元件的 `ConfigProvider` 會透過 Composer 套件發現機制自動註冊切面。切面和監聽器依賴 Hyperf 的 AOP
及事件調度器支援，標準 Hyperf 應用程式已提供這些支援；在最小化安裝中，請確保
`hyperf/di` 和 `hyperf/event` 可用。

## 自動派發

已註冊的切面會攔截 `Hyperf\ExceptionHandler\ExceptionHandlerDispatcher::dispatch()`。調度器正常返回後，
元件會派發 `ExceptionDispatched` 事件，其中包含已處理的異常，以及上下文中的目前請求和回應。

## 定義監聽器

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

## 事件屬性

`FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched` 公開以下屬性：

- `Throwable $throwable`：已處理或手動報告的異常。
- `?ServerRequestInterface $request`：目前請求；不在請求上下文中時為 `null`。
- `?ResponseInterface $response`：目前回應；不在回應上下文中時為 `null`。

## 手動報告

元件會自動載入三個命名空間輔助函數：

- `report(string|Throwable $exception = 'RuntimeException', ...$parameters)`：報告異常。
- `report_if($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)`：
  條件為真值時報告。
- `report_unless($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)`：
  條件為假值時報告。

```php
use DomainException;

use function FriendsOfHyperf\ExceptionEvent\report;
use function FriendsOfHyperf\ExceptionEvent\report_if;
use function FriendsOfHyperf\ExceptionEvent\report_unless;

report(new DomainException('The operation failed.'));
report('The operation failed.'); // 使用此訊息報告 RuntimeException。
report(DomainException::class, 'The operation failed.');

report_if($shouldReport, 'The operation failed.');
report_unless($operationSucceeded, 'The operation failed.');
```

`report()` 會直接派發 `ExceptionDispatched`，不會拋出異常。當第一個參數是已存在的異常類別名稱時，
其餘參數會傳給該異常的建構函數；其他字串會成為 `RuntimeException` 的訊息。

`report_if()` 在條件為真值時報告，`report_unless()` 在條件為假值時報告。兩個函數都會返回原始條件。
