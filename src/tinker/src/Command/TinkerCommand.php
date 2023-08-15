<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tinker\Command;

use FriendsOfHyperf\Tinker\ClassAliasAutoloader;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psy\Configuration;
use Psy\Shell;
use Psy\VersionUpdater\Checker;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

use function Hyperf\Support\env;

class TinkerCommand extends HyperfCommand
{
    /**
     * Commands to include in the tinker shell.
     * @var string[]
     */
    protected array $commandWhitelist = [
        'migrate',
    ];

    /**
     * Default casters.
     * @var string[]
     */
    protected array $defaultCasters = [
        'Hyperf\DbConnection\Model\Model' => 'FriendsOfHyperf\Tinker\TinkerCaster::castModel',
        'Hyperf\Redis\Redis' => 'FriendsOfHyperf\Tinker\TinkerCaster::castRedis',
        'Hyperf\Utils\Collection' => 'FriendsOfHyperf\Tinker\TinkerCaster::castCollection',
        'Hyperf\Collection\Collection' => 'FriendsOfHyperf\Tinker\TinkerCaster::castCollection',
        'Hyperf\Utils\Stringable' => 'FriendsOfHyperf\Tinker\TinkerCaster::castStringable',
        'Hyperf\Stringable\Stringable' => 'FriendsOfHyperf\Tinker\TinkerCaster::castStringable',
        'Hyperf\ViewEngine\HtmlString' => 'FriendsOfHyperf\Tinker\TinkerCaster::castHtmlString',
        'Symfony\Component\Console\Application' => 'FriendsOfHyperf\Tinker\TinkerCaster::castApplication',
    ];

    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        parent::__construct('tinker');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Interact with your application');
        $this->addOption('execute', null, InputOption::VALUE_OPTIONAL, 'Execute the given code using Tinker');
        $this->addArgument('include', InputArgument::IS_ARRAY, 'Include file(s) before starting tinker');
    }

    public function handle()
    {
        $this->getApplication()->setCatchExceptions(false);

        $config = Configuration::fromInput($this->input);
        $config->setUpdateCheck(Checker::NEVER);
        $config->setUsePcntl((bool) $this->config->get('tinker.usePcntl', false));
        $config->getPresenter()->addCasters(
            $this->getCasters()
        );

        if ($this->input->getOption('execute')) {
            $config->setRawOutput(true);
        }

        $shell = new Shell($config);

        $shell->addCommands($this->getCommands());
        $shell->setIncludes($this->input->getArgument('include'));

        $path = env('COMPOSER_VENDOR_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'vendor');
        $path .= '/composer/autoload_classmap.php';

        $loader = ClassAliasAutoloader::register(
            $shell,
            $path,
            $this->config->get('tinker.alias', []),
            $this->config->get('tinker.dont_alias', [])
        );

        if ($code = $this->input->getOption('execute')) {
            try {
                $shell->setOutput($this->output);
                $shell->execute($code, true);
            } catch (Throwable $e) {
                $shell->writeException($e);
                return 1;
            } finally {
                $loader->unregister();
            }

            return 0;
        }

        try {
            return $shell->run();
        } finally {
            $loader->unregister();
        }
    }

    /**
     * @return SymfonyCommand[]
     * @throws LogicException
     * @throws CommandNotFoundException
     */
    protected function getCommands(): array
    {
        $commands = [];

        $this->commandWhitelist = array_merge($this->commandWhitelist, (array) $this->config->get('tinker.command_white_list', []));

        foreach ($this->getApplication()->all() as $name => $command) {
            if (in_array($name, $this->commandWhitelist)) {
                $commands[] = $command;
            }
        }

        foreach ($this->config->get('tinker.commands', []) as $command) {
            $commands[] = $this->container->get($command);
        }

        return $commands;
    }

    /**
     * Get an array of Hyperf tailored casters.
     */
    protected function getCasters(): array
    {
        return array_merge($this->defaultCasters, (array) $this->config->get('tinker.casters', []));
    }
}
