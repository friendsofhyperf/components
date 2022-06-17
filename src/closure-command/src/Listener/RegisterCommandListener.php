<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand\Listener;

use FriendsOfHyperf\ClosureCommand\Annotation\Command;
use FriendsOfHyperf\ClosureCommand\AnnotationCommand;
use FriendsOfHyperf\ClosureCommand\Console;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

#[Listener]
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

        $this->logger->debug(sprintf('[closure-command] Commands registered by %s', __CLASS__));
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
        $methods = AnnotationCollector::getMethodsByAnnotation(Command::class);

        foreach ($methods as $method) {
            $reflector = new ReflectionMethod($method['class'], $method['method']);

            if (! $reflector->isPublic()) {
                continue;
            }

            $command = new AnnotationCommand(
                $this->container,
                $method['annotation']->signature,
                $method['class'],
                $method['method'],
                $method['annotation']->description
            );

            $commandId = sprintf('%s@%s', $method['class'], $method['method']);

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
