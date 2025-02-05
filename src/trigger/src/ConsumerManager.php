<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

use FriendsOfHyperf\Trigger\Contract\LoggerInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class ConsumerManager
{
    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config
    ) {
    }

    public function register(): void
    {
        /** @var array<string,array> */
        $connections = $this->config->get('trigger.connections', []);

        foreach ($connections as $connection => $options) {
            if (isset($options['enable']) && ! $options['enable']) {
                continue;
            }

            $process = $this->createProcess($connection, $options);

            ProcessManager::register($process);
        }
    }

    protected function createProcess(string $connection, array $options = []): AbstractProcess
    {
        return new class($this->container, $connection, $options) extends AbstractProcess {
            public function __construct(ContainerInterface $container, protected string $connection, protected array $options)
            {
                parent::__construct($container);
                $this->name = $options['name'] ?? 'trigger' . $connection;
                $this->nums = 1;
            }

            public function handle(): void
            {
                $consumer = make(Consumer::class, [
                    'connection' => $this->connection,
                    'options' => (array) $this->options,
                    'logger' => $this->container->has(LoggerInterface::class) ? $this->container->get(LoggerInterface::class) : null,
                ]);

                $consumer->start();
            }
        };
    }
}
