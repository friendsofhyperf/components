<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd\Command;

use FriendsOfHyperf\Confd\Confd;
use FriendsOfHyperf\Confd\Writer\EnvWriter;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Support\make;

class EnvCommand extends HyperfCommand
{
    protected ?string $signature = 'confd:env {--E|env-path= : Path of .env.}';

    protected string $description = 'Upgrade .env by confd.';

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected StdoutLoggerInterface $logger,
        protected Confd $confd
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $path = (string) ($this->input->getOption('env-path') ?? $this->config->get('confd.env_path'));

        if (! is_file($path)) {
            $this->error('The env file "' . $path . '" is not exists!');
            return $this->exitCode = 1;
        }

        try {
            $writer = $this->makeWriter($path);
            $values = $this->confd->fetch();

            $writer->setValues($values)->write();

            $this->logger->info($path . ' is updated.');
        } catch (Throwable $e) {
            $this->logger->error((string) $e);
            return $this->exitCode = 1;
        }
    }

    public function makeWriter(string $path): EnvWriter
    {
        return make(EnvWriter::class, compact('path'));
    }
}
