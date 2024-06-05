<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Mail\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Collection\collect;

class MailCommand extends GeneratorCommand
{
    protected string $description = 'Generate a new mail class';

    public function __construct(
        private readonly Filesystem $files,
        private readonly ConfigInterface $config
    ) {
        parent::__construct('gen:mail');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? ($this->input->getOption('markdown') !== false ? __DIR__ . '/stubs/mail.stub' : __DIR__ . '/stubs/markdown-mail.stub');
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\Mail';
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();
        return array_merge($options, [
            ['markdown', 'm', InputOption::VALUE_NONE, 'Create a new Markdown mailer class'],
        ]);
    }

    /**
     * Write the Markdown template for the mailable.
     */
    protected function writeMarkdownTemplate(): void
    {
        $path = $this->viewPath(
            str_replace('.', '/', $this->getView()) . '.blade.php'
        );

        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }

        $this->files->put($path, file_get_contents(__DIR__ . '/stubs/markdown.stub'));
    }

    /**
     * Get the first view directory path from the application configuration.
     */
    protected function viewPath(string $path = ''): string
    {
        $views = $this->config->get('view.paths.0') ?? BASE_PATH . '/storage/views';

        return $views . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the view name.
     */
    protected function getView(): string
    {
        $view = $this->input->getOption('markdown');

        if (! $view) {
            $name = str_replace('\\', '/', $this->input->getArgument('name'));

            $view = 'mail.' . collect(explode('/', $name))
                ->map(fn ($part) => Str::kebab($part))
                ->implode('.');
        }

        return $view;
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $name): string
    {
        $class = str_replace(
            '{{ subject }}',
            Str::headline(str_replace($this->getNamespace($name) . '\\', '', $name)),
            parent::buildClass($name)
        );

        if ($this->input->getOption('markdown') !== false) {
            $class = str_replace(['DummyView', '{{ view }}'], $this->getView(), $class);
        }

        return $class;
    }
}
