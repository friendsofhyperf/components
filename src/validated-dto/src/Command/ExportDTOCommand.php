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

use FriendsOfHyperf\ValidatedDTO\Exporter\ExporterInterface;
use FriendsOfHyperf\ValidatedDTO\Exporter\TypeScriptExporter;
use Hyperf\Contract\ConfigInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDTOCommand extends SymfonyCommand
{
    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(protected ConfigInterface $config, protected TypeScriptExporter $typeScriptExporter)
    {
        parent::__construct('dto:export');
    }

    public function configure()
    {
        foreach ($this->getArguments() as $argument) {
            $this->addArgument(...$argument);
        }

        foreach ($this->getOptions() as $option) {
            $this->addOption(...$option);
        }

        $this->setDescription('Export DTO classes to various formats.');
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
        $lang = $input->getOption('lang');

        try {
            $exporter = $this->getExporter($lang);
            $exported = $exporter->export($class);
            
            if ($outputPath) {
                $this->writeToFile($outputPath, $exported);
                $output->writeln(sprintf('<info>DTO exported to %s</info>', $outputPath));
            } else {
                $output->writeln($exported);
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

    protected function getExporter(string $lang): ExporterInterface
    {
        return match ($lang) {
            'typescript', 'ts' => $this->typeScriptExporter,
            default => throw new \InvalidArgumentException("Unsupported language: {$lang}"),
        };
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
            ['output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files'],
            ['lang', 'l', InputOption::VALUE_OPTIONAL, 'Export language (typescript|ts)', 'typescript'],
        ];
    }
}