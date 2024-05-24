<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Command;

use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class GenNotificationCommand extends Command
{
    protected ?string $name = 'gen:notification';

    protected string $description = 'Generate a new notification class';

    public function handle()
    {
        $name = $this->input->getArgument('name');
        $force = $this->input->getOption('force');
        $namespace = $this->input->getOption('namespace');
        $realpath = $this->input->getOption('realpath');
        $path = BASE_PATH . $realpath . '/' . $name . '.php';

        if (file_exists($path) && ! $force) {
            $this->output->error('Notification already exists!');
            return;
        }

        $content = file_get_contents(__DIR__ . '/Stubs/notification.stub');
        $content = str_replace('{{ namespace }}', $namespace, $content);
        $content = str_replace('{{ class }}', $name, $content);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $content);

        $this->output->success('Notification created successfully!');
    }

    protected function configure()
    {
        $this->setDescription($this->description);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the notification class');
        $this->addOption('force', 'f', null, 'Overwrite the notification if it exists');
        $this->addOption('namespace', 'ns', InputArgument::OPTIONAL, 'The namespace of the notification class', 'App\\Notifications');
        $this->addOption('realpath', 'rp', InputArgument::OPTIONAL, 'The realpath of the notification class', '/app/Notifications');
    }
}
