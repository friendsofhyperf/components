<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Command;

use FriendsOfHyperf\ValidatedDTO\Export\TypeScriptExporter;
use Hyperf\Contract\ConfigInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDTOToTypescriptCommand extends SymfonyCommand
{
    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(protected ConfigInterface $config, protected TypeScriptExporter $exporter)
    {
        parent::__construct('dto:export-ts');
    }

    public function configure()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption(...$option);
        }

        $this->setDescription('Export DTO classes to TypeScript interfaces.');
        $this->setAliases([]);
    }

    /**
     * Execute the console command.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $class = $input->getArgument('class');
        $outputPath = $input->getOption('output');

        try {
            $typescript = $this->exporter->export($class);
            
            if ($outputPath) {
                $this->writeToFile($outputPath, $typescript);
                $output->writeln(sprintf('<info>TypeScript interface exported to %s</info>', $outputPath));
            } else {
                $output->writeln($typescript);
            }

            return 0;
        } catch (\InvalidArgumentException $e) {
            $output->writeln(sprintf('<fg=red>%s</>', $e->getMessage()));
            return 1;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return 1;
        }
    }

    protected function writeToFile(string $path, string $content): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (file_exists($path) && !$this->input->getOption('force')) {
            $this->output->writeln(sprintf('<error>File %s already exists! Use --force to overwrite.</error>', $path));
            return;
        }

        file_put_contents($path, $content);
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['class', InputArgument::REQUIRED, 'The fully qualified class name of the DTO'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path for the TypeScript interface', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
        ];
    }
}