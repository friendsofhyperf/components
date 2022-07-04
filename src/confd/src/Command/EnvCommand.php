<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd\Command;

use FriendsOfHyperf\Confd\Confd;
use FriendsOfHyperf\Confd\DotEnv\Writer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;

#[Command()]
class EnvCommand extends HyperfCommand
{
    protected ?string $signature = 'confd:env {--E|env-path= : Path of .env.}';

    protected string $description = 'Upgrade .env from etcd.';

    protected ConfigInterface $config;

    protected StdoutLoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);

        parent::__construct();
    }

    public function handle()
    {
        $path = (string) ($this->input->getOption('env-path') ?? $this->config->get('confd.env_path'));

        if (! is_file($path)) {
            $this->error($path . ' is not exists!');
            return;
        }

        $writer = $this->makeWriter($path);
        $confd = $this->container->get(Confd::class);

        $values = $confd->fetch();

        $writer->setValues($values)->write();

        $this->logger->info($path . ' is updated.');
    }

    public function makeWriter(string $path): Writer
    {
        return make(Writer::class, [
            'path' => $path,
        ]);
    }
}
