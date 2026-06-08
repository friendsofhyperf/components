# Exception Event

This component dispatches an `ExceptionDispatched` event after Hyperf's exception handler
dispatcher handles an exception. It also provides helper functions for manually reporting an
exception without throwing it.

## Installation

```shell
composer require friendsofhyperf/exception-event
```

The component's `ConfigProvider` registers its aspect automatically through Composer package
discovery. The aspect and listeners require Hyperf's AOP and event dispatcher support, which a
standard Hyperf application already provides. For a minimal installation, ensure `hyperf/di` and
`hyperf/event` are available.

## Automatic Dispatching

The registered aspect intercepts `Hyperf\ExceptionHandler\ExceptionHandlerDispatcher::dispatch()`.
After the dispatcher returns normally, the component dispatches an `ExceptionDispatched` event
containing the handled exception and the current request and response from the context.

## Defining a Listener

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

## Event Properties

`FriendsOfHyperf\ExceptionEvent\Event\ExceptionDispatched` exposes these public properties:

- `Throwable $throwable`: the handled or manually reported exception.
- `?ServerRequestInterface $request`: the current request, or `null` outside a request context.
- `?ResponseInterface $response`: the current response, or `null` outside a response context.

## Manual Reporting

The component autoloads three namespaced helper functions:

- `report(string|Throwable $exception = 'RuntimeException', ...$parameters)` reports an exception.
- `report_if($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)` reports
  when the condition is truthy.
- `report_unless($condition, string|Throwable $exception = 'RuntimeException', ...$parameters)`
  reports when the condition is falsy.

```php
use DomainException;

use function FriendsOfHyperf\ExceptionEvent\report;
use function FriendsOfHyperf\ExceptionEvent\report_if;
use function FriendsOfHyperf\ExceptionEvent\report_unless;

report(new DomainException('The operation failed.'));
report('The operation failed.'); // Reports a RuntimeException with this message.
report(DomainException::class, 'The operation failed.');

report_if($shouldReport, 'The operation failed.');
report_unless($operationSucceeded, 'The operation failed.');
```

`report()` dispatches `ExceptionDispatched` directly and does not throw the exception. When its
first argument is an existing exception class name, the remaining arguments are passed to that
exception's constructor. Any other string becomes the message of a `RuntimeException`.

`report_if()` reports when its condition is truthy, while `report_unless()` reports when its
condition is falsy. Both functions return the original condition.
