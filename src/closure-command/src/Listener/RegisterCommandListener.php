<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand\Listener;

use FriendsOfHyperf\ClosureCommand\Annotation\CommandCollector;
use FriendsOfHyperf\ClosureCommand\AnnotationCommand;
use FriendsOfHyperf\ClosureCommand\Console;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

class RegisterCommandListener implements ListenerInterface
{
    /**
     * @param \Hyperf\Di\Container $container
     */
    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $this->registerClosureCommands();
        $this->registerAnnotationCommands();

        $this->logger->debug(sprintf('[closure-command] Commands registered by %s', self::class));
    }

    private function registerClosureCommands(): void
    {
        $route = BASE_PATH . '/config/console.php';

        if (! file_exists($route)) {
            return;
        }

        require_once $route;

        foreach (Console::getCommands() as $handlerId => $command) {
            $this->container->set($handlerId, $command);
            $this->appendConfig('commands', $handlerId);
        }
    }

    private function registerAnnotationCommands(): void
    {
        $commands = CommandCollector::list();

        foreach ($commands as $commandId => $metadata) {
            $command = new AnnotationCommand(
                $this->container,
                $metadata['signature'],
                $metadata['class'],
                $metadata['method'],
                $metadata['description'] ?? ''
            );

            $this->container->set($commandId, $command);
            $this->appendConfig('commands', $commandId);
        }
    }

    private function appendConfig(string $key, $configValues)
    {
        $configs = $this->config->get($key, []);
        array_push($configs, $configValues);
        $this->config->set($key, $configs);
    }
}
