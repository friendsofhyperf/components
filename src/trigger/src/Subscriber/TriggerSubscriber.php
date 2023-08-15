<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Subscriber;

use FriendsOfHyperf\Trigger\Consumer;
use FriendsOfHyperf\Trigger\Traits\Logger;
use FriendsOfHyperf\Trigger\TriggerManager;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Concurrent;
use MySQLReplication\Definitions\ConstEventsNames;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Support\call;

class TriggerSubscriber extends AbstractSubscriber
{
    use Logger;

    protected Concurrent $concurrent;

    public function __construct(
        protected ContainerInterface $container,
        protected TriggerManager $triggerManager,
        protected Consumer $consumer,
        protected ?StdoutLoggerInterface $logger = null
    ) {
        $this->concurrent = new Concurrent(
            (int) $consumer->getOption('concurrent.limit') ?? 1000
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConstEventsNames::UPDATE => 'onUpdate',
            ConstEventsNames::DELETE => 'onDelete',
            ConstEventsNames::WRITE => 'onWrite',
        ];
    }

    protected function allEvents(EventDTO $event): void
    {
        if (! $event instanceof RowsDTO) {
            return;
        }

        $key = join('.', [
            $this->consumer->getConnection(),
            $event->getTableMap()->getDatabase(),
            $event->getTableMap()->getTable(),
            $event->getType(),
        ]);

        $eventType = $event->getType();

        foreach ($this->triggerManager->get($key) as $callable) {
            foreach ($event->getValues() as $value) {
                $this->concurrent->create(function () use ($callable, $value, $eventType) {
                    [$class, $method] = $callable;

                    if (! $this->container->has($class)) {
                        $this->warning(sprintf('Entry "%s" cannot be resolved.', $class));
                        return;
                    }

                    $args = match ($eventType) {
                        ConstEventsNames::WRITE => [$value],
                        ConstEventsNames::UPDATE => [$value['before'], $value['after']],
                        ConstEventsNames::DELETE => [$value],
                        default => null,
                    };

                    if (! $args) {
                        return;
                    }

                    try {
                        call([$this->container->get($class), $method], $args);
                    } catch (Throwable $e) {
                        $this->error(sprintf(
                            "%s in %s:%s\n%s",
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine(),
                            $e->getTraceAsString()
                        ));
                    }
                });
            }
        }
    }
}
