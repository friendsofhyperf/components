<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand\Listener;

use FriendsOfHyperf\ClosureCommand\Console;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
#[Listener]
class RegisterCommandListener implements ListenerInterface
{
    /**
     * @var \Hyperf\Contract\ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
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

        $this->logger->debug(sprintf('[closure-command] Commands registered by %s', __CLASS__));
    }

    private function appendConfig(string $key, $configValues)
    {
        $configs = $this->config->get($key, []);
        array_push($configs, $configValues);
        $this->config->set($key, $configs);
    }
}
