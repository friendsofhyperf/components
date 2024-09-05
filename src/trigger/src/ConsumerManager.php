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
        protected ConfigInterface $config,
        protected ?LoggerInterface $logger = null
    ) {
    }

    public function run()
    {
        $connections = $this->config->get('trigger.connections', []);

        foreach ($connections as $connection => $options) {
            if (isset($options['enable']) && ! $options['enable']) {
                continue;
            }

            $consumer = make(Consumer::class, [
                'connection' => $connection,
                'options' => (array) $options,
                'logger' => $this->logger,
            ]);

            $process = $this->createProcess($consumer);
            $process->name = $consumer->getName();
            $process->nums = 1;

            ProcessManager::register($process);
        }
    }

    protected function createProcess(Consumer $consumer): AbstractProcess
    {
        return new class($this->container, $consumer) extends AbstractProcess {
            public function __construct(ContainerInterface $container, protected Consumer $consumer)
            {
                parent::__construct($container);
            }

            public function handle(): void
            {
                $this->consumer->start();
            }
        };
    }
}
