<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Command;

use Hyperf\Command\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class InstallCommand extends Command
{
    public function __construct(private ContainerInterface $container)
    {
        parent::__construct('telescope:install');
    }

    public function handle()
    {
        /** @var \Symfony\Component\Console\Application $application */
        $application = $this->container->get(\Hyperf\Contract\ApplicationInterface::class);
        $application->setAutoExit(false);

        $output = new NullOutput();

        $input = new ArrayInput(['command' => 'vendor:publish', 'package' => 'guandeng/hyperf-telescope']);
        $exitCode = $application->run($input, $output);
        if (! $exitCode) {
            $this->info('publish successfully');
        } else {
            $this->error('publish failed');
        }

        $input = new ArrayInput(['command' => 'migrate']);
        $exitCode = $application->run($input, $output);
        if (! $exitCode) {
            $this->info('migrate successfully');
        } else {
            $this->error('migrate failed');
        }
    }
}
