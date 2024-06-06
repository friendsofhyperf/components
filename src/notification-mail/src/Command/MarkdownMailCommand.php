<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Mail\Command;

use Hyperf\Command\Command;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

class MarkdownMailCommand extends Command
{
    protected ?string $name = 'gen:markdown-mail';

    protected string $description = 'Generate a new Markdown mail class';

    public function __invoke()
    {
        $stub = __DIR__ . '/stubs/markdown-notification.stub';
        $name = $this->input->getArgument('name');
        $force = $this->input->getOption('force');
        $namespace = $this->input->getOption('namespace');
        $realpath = $this->input->getOption('realpath');
        $view = $this->input->getOption('view');
        $path = BASE_PATH . $realpath . '/' . $name . '.php';
        if (file_exists($path) && ! $force) {
            $this->output->error('Mail already exists!');
            return;
        }
        $content = file_get_contents($stub);
        $content = str_replace(['{{ namespace }}', '{{ class }}', '{{ view }}'], [$namespace, $name, $view], $content);
        if (! is_dir(dirname($path))) {
            if (! mkdir($concurrentDirectory = dirname($path), 0777, true) && ! is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        file_put_contents($path, $content);
        $this->output->success('Mail created successfully!');
    }

    protected function configure(): void
    {
        $this->setDescription($this->description);
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the mail class');
        $this->addOption('force', 'f', null, 'Overwrite the mail if it exists');
        $this->addOption('namespace', 'ns', InputArgument::OPTIONAL, 'The namespace of the mail class', 'App\\Mail');
        $this->addOption('realpath', 'rp', InputArgument::OPTIONAL, 'The realpath of the mail class', '/app/Mail');
        $this->addOption('view', 'view', InputArgument::OPTIONAL, 'The view of the mail class', 'emails');
    }
}
