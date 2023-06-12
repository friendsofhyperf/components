<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Trigger\Command;

use FriendsOfHyperf\CommandSignals\Traits\InteractsWithSignals;
use FriendsOfHyperf\Trigger\Consumer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

#[Command()]
class ConsumeCommand extends HyperfCommand
{
    use InteractsWithSignals;

    protected ?string $signature = 'trigger:consume';

    protected string $description = 'Run consumers.';

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config
    ) {
    }

    public function handle()
    {
        $consumers = $this->getConsumers();

        $this->trap([SIGINT, SIGTERM], function ($signo) use ($consumers) {
            $this->warn(sprintf('Received signal %d, exiting...', $signo));

            foreach ($consumers as $consumer) {
                $consumer->stop();
            }

            CoordinatorManager::until(self::class)->resume();
        });

        foreach ($consumers as $consumer) {
            Coroutine::create(function () use ($consumer) {
                $consumer->start();
            });
        }

        CoordinatorManager::until(self::class)->yield();

        $this->info('Bye!');
    }

    /**
     * @return Consumer[]
     */
    public function getConsumers(): array
    {
        $consumers = [];
        $connections = $this->config->get('trigger.connections', []);

        foreach ($connections as $connection => $options) {
            if (isset($options['enable']) && ! $options['enable']) {
                continue;
            }

            $consumers[] = make(Consumer::class, [
                'connection' => $connection,
                'options' => (array) $options,
            ]);
        }

        return $consumers;
    }
}
