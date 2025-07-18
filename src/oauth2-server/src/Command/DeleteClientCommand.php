<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Command;

use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DeleteClientCommand extends Command
{
    protected ?string $name = 'oauth2-server:delete-client';

    public function __construct(
        private readonly ClientManagerInterface $clientManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deletes an OAuth2 client')
            ->addArgument('identifier', InputArgument::REQUIRED, 'The client identifier')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force deletion without confirmation');
    }

    protected function handle(): int
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $identifier = $this->input->getArgument('identifier');
        $force = $this->input->getOption('force');
        if (null === $client = $this->clientManager->find($identifier)) {
            $io->error(\sprintf('OAuth2 client identified as "%s" does not exist.', $identifier));
            return Command::FAILURE;
        }
        if (! $force) {
            if (! $io->confirm(\sprintf('Are you sure you want to delete the OAuth2 client identified as "%s"?', $identifier), false)) {
                $io->warning('OAuth2 client deletion cancelled.');
                return Command::FAILURE;
            }
        }
        $this->clientManager->remove($client);
        $io->success('OAuth2 client deleted successfully.');
        return Command::SUCCESS;
    }
}
