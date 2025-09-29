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

use Exception;
use FriendsOfHyperf\ValidatedDTO\Exporter\ExporterInterface;
use FriendsOfHyperf\ValidatedDTO\Exporter\TypeScriptExporter;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDTOCommand extends SymfonyCommand
{
    public const DEFAULT_LANGUAGE = 'typescript';

    public const SUPPORTED_LANGUAGES = ['typescript', 'ts'];

    public const FILE_PERMISSIONS = 0755;

    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('dto:export');
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
        } catch (InvalidArgumentException $e) {
            $output->writeln(sprintf('<fg=red>%s</>', $e->getMessage()));
            return 1;
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
            return 1;
        }
    }

    protected function configure(): void
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

    protected function getExporter(string $lang): ExporterInterface
    {
        return match ($lang) {
            'typescript', 'ts' => $this->container->get(TypeScriptExporter::class),
            default => throw new InvalidArgumentException("Unsupported language: {$lang}"),
        };
    }

    protected function writeToFile(string $path, string $content): void
    {
        // Validate path to prevent directory traversal attacks
        if (str_contains($path, '..') || str_contains($path, '~')) {
            throw new InvalidArgumentException('Invalid file path provided.');
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, self::FILE_PERMISSIONS, true);
        }

        if (file_exists($path) && ! $this->input->getOption('force')) {
            throw new RuntimeException(sprintf('File %s already exists! Use --force to overwrite.', $path));
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
            ['lang', 'l', InputOption::VALUE_OPTIONAL, 'Export language (typescript|ts)', self::DEFAULT_LANGUAGE],
        ];
    }
}
