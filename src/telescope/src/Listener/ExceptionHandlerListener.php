<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event;
use Hyperf\Stringable\Str;
use Throwable;

class ExceptionHandlerListener implements ListenerInterface
{
    public function __construct(private TelescopeConfig $telescopeConfig)
    {
    }

    public function listen(): array
    {
        return [
            Event\RequestTerminated::class,
        ];
    }

    /**
     * @param Event\RequestTerminated|object $event
     */
    public function process(object $event): void
    {
        if (
            ! $event instanceof Event\RequestTerminated
            || ! $this->telescopeConfig->isEnable('exception')
            || ! TelescopeContext::getBatchId()
        ) {
            return;
        }

        if (! $exception = $event->getThrowable()) {
            return;
        }

        $trace = (new Collection($exception->getTrace()))
            ->map(fn ($item) => Arr::only($item, ['file', 'line']))
            ->toArray();

        Telescope::recordException(IncomingEntry::make([
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'context' => null,
            'trace' => $trace,
            'line_preview' => $this->getContext($exception),
        ]));
    }

    /**
     * @param Throwable $exception
     */
    protected function getContext($exception): array
    {
        if (Str::contains($exception->getFile(), "eval()'d code")) {
            return [
                $exception->getLine() => "eval()'d code",
            ];
        }

        return (new Collection(explode("\n", file_get_contents($exception->getFile()))))
            ->slice($exception->getLine() - 10, 20)
            ->mapWithKeys(fn ($value, $key) => [$key + 1 => $value])
            ->all();
    }
}
